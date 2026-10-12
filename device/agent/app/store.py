from pathlib import Path
import json, os, shutil, hashlib

class Store:
    def __init__(self, root: Path):
        self.root=root; self.releases=root/'releases'; self.current=root/'current'
        self.releases.mkdir(parents=True, exist_ok=True)
    def version(self):
        p=self.current/'manifest.json'
        if not p.exists(): return None
        try: return json.loads(p.read_text())['version']
        except Exception: return None
    def stage(self, manifest, fetch):
        version=str(manifest['version']); staging=self.root/f'.staging-{version}'
        shutil.rmtree(staging, ignore_errors=True); staging.mkdir(parents=True)
        for a in manifest.get('assets',[]):
            rel=Path(a['path']); dest=staging/rel; dest.parent.mkdir(parents=True,exist_ok=True)
            body=fetch(a['url']); dest.write_bytes(body)
            digest=hashlib.sha256(body).hexdigest()
            if digest.lower()!=a['sha256'].lower(): raise ValueError(f'checksum mismatch: {rel}')
        (staging/'manifest.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2),encoding='utf-8')
        release=self.releases/version; shutil.rmtree(release,ignore_errors=True); os.replace(staging,release)
        tmp=self.root/'.current-new'; tmp.unlink(missing_ok=True); tmp.symlink_to(release, target_is_directory=True)
        os.replace(tmp,self.current)
        self.prune(keep=2); return version
    def prune(self, keep=2):
        dirs=sorted([p for p in self.releases.iterdir() if p.is_dir()], key=lambda p:p.stat().st_mtime, reverse=True)
        for p in dirs[keep:]: shutil.rmtree(p,ignore_errors=True)
