import json, urllib.request
from urllib.parse import urljoin, urlsplit
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        raise ValueError('redirects are not allowed for authenticated device requests')
class SyncClient:
    def __init__(self,cfg): self.cfg=cfg
    def _request(self,url,method='GET',payload=None):
        headers={'Authorization':f'Bearer {self.cfg.device_token}','X-Device-ID':self.cfg.device_id,'User-Agent':'cantinho-signage/1','Accept':'application/json'}
        body=None
        if payload is not None:
            body=json.dumps(payload).encode(); headers['Content-Type']='application/json'
        req=urllib.request.Request(url,data=body,headers=headers,method=method)
        with urllib.request.build_opener(NoRedirect).open(req,timeout=10) as r: return r.read()
    def manifest(self): return json.loads(self._request(f'{self.cfg.server_url}/api/signage/manifest/{self.cfg.device_id}'))
    def fetch(self,url):
        url=urljoin(self.cfg.server_url+'/',url)
        if urlsplit(url).netloc != urlsplit(self.cfg.server_url).netloc or urlsplit(url).scheme != urlsplit(self.cfg.server_url).scheme:
            raise ValueError('asset URL must use the configured server origin')
        return self._request(url)
    def heartbeat(self,status):
        return json.loads(self._request(f'{self.cfg.server_url}/api/signage/heartbeat/{self.cfg.device_id}','POST',status))
