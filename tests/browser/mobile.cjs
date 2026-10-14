const fs=require('node:fs');
const path=require('node:path');
const {spawn,spawnSync}=require('node:child_process');
const assert=require('node:assert/strict');
const {chromium}=require('playwright');

async function main(){
 const root=path.resolve(__dirname,'../..');
 const artifactDir=path.join(root,'storage/framework/testing/browser');
 fs.mkdirSync(artifactDir,{recursive:true});
 const db=path.join(artifactDir,`mobile-${process.pid}.sqlite`); fs.writeFileSync(db,'');
 const env={...process.env,APP_ENV:'local',APP_DEBUG:'false',APP_KEY:'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',APP_URL:'http://127.0.0.1:8011',DB_CONNECTION:'sqlite',DB_DATABASE:db,CACHE_STORE:'array',SESSION_DRIVER:'database',QUEUE_CONNECTION:'sync',PUBLIC_MEDIA_ROOT:path.join(artifactDir,'media-'+process.pid)};
 const run=(args)=>{const r=spawnSync('php',args,{cwd:root,env,encoding:'utf8'});if(r.status!==0)throw Error(r.stderr||r.stdout);};
 run(['artisan','migrate','--force','--no-interaction']); run(['tests/browser/seed.php']);
 const server=spawn('php',['artisan','serve','--host=127.0.0.1','--port=8011','--no-reload'],{cwd:root,env,stdio:'ignore',windowsHide:true,detached:process.platform!=='win32'});
 let browser;
 try {
  for(let tries=0;tries<40;tries++){
   try{const r=await fetch('http://127.0.0.1:8011/health');if(r.ok)break;}catch{}
   if(tries===39)throw Error('Isolated test server did not start');
   await new Promise(r=>setTimeout(r,250));
  }
  browser=await chromium.launch({executablePath:process.env.BROWSER_EXECUTABLE||(process.platform==='win32'?'C:/Program Files/Google/Chrome/Application/chrome.exe':undefined),headless:true});
  const context=await browser.newContext({viewport:{width:390,height:844},deviceScaleFactor:1});
  const page=await context.newPage();
  await page.goto('http://127.0.0.1:8011/entrar');
  await page.getByLabel('Email',{exact:true}).fill('browser@example.test');
  await page.getByLabel('Palavra-passe',{exact:true}).fill('Browser-test-password-123');
  await page.getByRole('button',{name:'Entrar',exact:true}).click();
  await page.waitForURL('**/operacao');
  await page.getByText('Publicação agendada',{exact:false}).waitFor();
  const mainAction=await page.getByRole('link',{name:'Editar pratos de hoje',exact:true}).boundingBox();
  const navigation=await page.getByRole('navigation',{name:'Operação',exact:true}).boundingBox();
  assert(mainAction.y+mainAction.height<navigation.y,'Primary daily action must be visible above mobile navigation');
  await page.screenshot({path:path.join(artifactDir,'dashboard-mobile.png'),fullPage:true});
  for(const [label,url] of [['Hoje','pratos-de-hoje'],['Conteúdos','conteudos'],['Fotografias','fotografias'],['Clientes','fidelizacao'],['Definições','definicoes'],['Início','']]){
   await page.getByRole('navigation',{name:'Operação',exact:true}).getByRole('link',{name:label,exact:true}).click();
   await page.waitForURL('**/operacao'+(url?'/'+url:''));
   assert.equal(await page.locator('main').count(),1);
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,`Horizontal overflow on ${label}`);
  }
  for(const width of [320,768,1280]){
   await page.setViewportSize({width,height:844});
   for(const url of ['/operacao','/operacao/pratos-de-hoje','/operacao/conteudos','/operacao/fotografias','/operacao/fidelizacao','/operacao/definicoes']){
    await page.goto('http://127.0.0.1:8011'+url);
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,`Horizontal overflow at ${width}px on ${url}`);
   }
  }
  await page.setViewportSize({width:390,height:844});
  await page.getByRole('navigation',{name:'Operação'}).getByRole('link',{name:'Hoje',exact:true}).click();
  await page.getByLabel('Prato 1',{exact:true}).fill('Prato do teste móvel');
  await page.getByLabel('Preço (€)',{exact:true}).fill('12.50');
  await page.getByRole('button',{name:'Publicar agora',exact:true}).click();
  await page.getByText('Pratos guardados e publicados',{exact:false}).waitFor();
  await page.screenshot({path:path.join(artifactDir,'daily-menu-mobile.png'),fullPage:true});
  await page.getByRole('button',{name:'Esgotou',exact:true}).click();
  await page.getByRole('button',{name:'Voltar a disponibilizar',exact:true}).waitFor();
  await page.goto('http://127.0.0.1:8011/');
  assert.equal(await page.getByText('Prato do teste móvel',{exact:true}).count(),0);
  await page.goto('http://127.0.0.1:8011/operacao');
  await page.getByRole('button',{name:'Sair',exact:true}).click();
  await page.waitForURL('**/entrar');
  await page.goto('http://127.0.0.1:8011/operacao'); await page.waitForURL('**/entrar');
  console.log('Mobile login, all navigation links, publication, sold-out state and logout passed at 390×844; no horizontal overflow at 320, 768 and 1280px.');
  console.log('Screenshots: '+artifactDir);
 } finally {
  if(browser)await browser.close();
  if(server.pid && process.platform==='win32')spawnSync('taskkill',['/PID',String(server.pid),'/T','/F'],{stdio:'ignore',windowsHide:true});
  else if(server.pid)process.kill(-server.pid,'SIGTERM');
 }
}
main().catch(error=>{console.error(error.message);process.exitCode=1;});
