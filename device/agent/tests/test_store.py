import tempfile, unittest, hashlib, os
from pathlib import Path
from app.store import Store

class StoreTest(unittest.TestCase):
    @unittest.skipIf(os.name == 'nt', 'Atomic symlink activation requires the target Linux runtime')
    def test_atomic_release_and_checksum(self):
        with tempfile.TemporaryDirectory() as d:
            s=Store(Path(d)); body=b'<h1>ok</h1>'; sha=hashlib.sha256(body).hexdigest()
            m={'version':'1','assets':[{'path':'index.html','url':'/index','sha256':sha}]}
            s.stage(m,lambda _:body)
            self.assertEqual(s.version(),'1'); self.assertEqual((s.current/'index.html').read_bytes(),body)
    def test_bad_checksum_does_not_activate(self):
        with tempfile.TemporaryDirectory() as d:
            s=Store(Path(d)); m={'version':'2','assets':[{'path':'index.html','url':'/x','sha256':'0'*64}]}
            with self.assertRaises(ValueError): s.stage(m,lambda _:b'bad')
            self.assertIsNone(s.version())

    @unittest.skipIf(os.name == 'nt', 'Atomic symlink activation requires the target Linux runtime')
    def test_nested_media_asset_is_staged(self):
        with tempfile.TemporaryDirectory() as d:
            s=Store(Path(d)); html=b'<img src="media/8.jpg">'; photo=b'jpeg-bytes'
            m={'version':'3','assets':[
                {'path':'index.html','url':'/index','sha256':hashlib.sha256(html).hexdigest()},
                {'path':'media/8.jpg','url':'/media/8','sha256':hashlib.sha256(photo).hexdigest()},
            ]}
            bodies={'/index':html,'/media/8':photo}
            s.stage(m,lambda url:bodies[url])
            self.assertEqual((s.current/'media/8.jpg').read_bytes(),photo)

    @unittest.skipIf(os.name == 'nt', 'Atomic symlink activation requires the target Linux runtime')
    def test_failed_new_release_keeps_previous_current(self):
        with tempfile.TemporaryDirectory() as d:
            s=Store(Path(d)); good=b'ok'
            s.stage({'version':'1','assets':[{'path':'index.html','url':'/ok','sha256':hashlib.sha256(good).hexdigest()}]},lambda _:good)
            bad={'version':'2','assets':[{'path':'index.html','url':'/bad','sha256':'0'*64}]}
            with self.assertRaises(ValueError): s.stage(bad,lambda _:b'broken')
            self.assertEqual(s.version(),'1')
            self.assertEqual((s.current/'index.html').read_bytes(),good)

    def test_untrusted_paths_are_rejected_before_download(self):
        with tempfile.TemporaryDirectory() as d:
            s=Store(Path(d))
            for path in ['../escape', '/absolute', 'media/../../escape', 'C:/escape', 'media\\escape', 'manifest.json']:
                with self.subTest(path=path), self.assertRaises(ValueError):
                    s.stage({'version':'safe','assets':[{'path':path}]},lambda _:self.fail('Must not fetch'))

    def test_untrusted_version_is_rejected(self):
        with tempfile.TemporaryDirectory() as d:
            s=Store(Path(d))
            for version in ['../escape','/absolute','a/b','']:
                with self.subTest(version=version), self.assertRaises(ValueError):
                    s.stage({'version':version,'assets':[]},lambda _:b'')

if __name__=='__main__': unittest.main()

