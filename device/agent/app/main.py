from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
import json, mimetypes, threading, time, os
from .config import Config
from .store import Store
from .sync import SyncClient

FALLBACK=b"""<!doctype html><html lang="pt"><meta charset="utf-8"><style>html,body{height:100%;margin:0;background:#F7F0E4;color:#241A16;font-family:system-ui}main{height:100%;display:grid;place-items:center;text-align:center}h1{font-size:5vw;color:#7A1E1E}p{font-size:2vw}</style><main><div><h1>O Cantinho da Mariana</h1><p>Pergunte-nos pelos pratos de hoje</p></div></main>"""

try: cfg=Config.load()
except Exception:
    cfg=Config('http://127.0.0.1','unconfigured','',Path('/tmp/cantinho'),60,8765)
store=Store(cfg.data_dir); state={'last_sync':None,'last_error':None}; PLAYER_VERSION='1.1.0'

def sync_loop():
    client=SyncClient(cfg)
    while True:
        try:
            m=client.manifest()
            if str(m.get('version')) != str(store.version()): store.stage(m,client.fetch)
            state.update(last_sync=int(time.time()),last_error=None)
            client.heartbeat({'player_version':PLAYER_VERSION,'content_version':store.version(),'last_sync':state['last_sync'],'last_error':None})
        except Exception as e:
            state['last_error']=str(e)[:300]
            try: client.heartbeat({'player_version':PLAYER_VERSION,'content_version':store.version(),'last_sync':state['last_sync'],'last_error':state['last_error']})
            except Exception: pass
        time.sleep(max(15,cfg.sync_seconds))

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path=='/health': return self.send_json({'ok':True,'version':store.version(),'last_sync':state['last_sync'],'last_error':state['last_error']})
        if self.path=='/version': return self.send_json({'player':PLAYER_VERSION,'content':store.version()})
        base=store.current if store.current.exists() else None
        rel='index.html' if self.path in ('/','') else self.path.lstrip('/')
        target=(base/rel).resolve() if base else None
        if target and str(target).startswith(str(base.resolve())) and target.is_file():
            body=target.read_bytes(); self.send_response(200); self.send_header('Content-Type',mimetypes.guess_type(str(target))[0] or 'application/octet-stream')
        else:
            body=FALLBACK; self.send_response(200); self.send_header('Content-Type','text/html; charset=utf-8')
        self.send_header('Cache-Control','no-store'); self.send_header('Content-Length',str(len(body))); self.end_headers(); self.wfile.write(body)
    def send_json(self,obj):
        body=json.dumps(obj).encode(); self.send_response(200); self.send_header('Content-Type','application/json'); self.send_header('Content-Length',str(len(body))); self.end_headers(); self.wfile.write(body)
    def log_message(self,*_): pass

if __name__=='__main__':
    threading.Thread(target=sync_loop,daemon=True).start()
    ThreadingHTTPServer(('127.0.0.1',cfg.port),Handler).serve_forever()
