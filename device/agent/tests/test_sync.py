import unittest
from pathlib import Path
from unittest.mock import patch
from app.config import Config
from app.sync import SyncClient, NoRedirect
from app.player import with_version_watcher

class SyncTest(unittest.TestCase):
    def setUp(self):
        self.client=SyncClient(Config('https://restaurant.example','device','secret',Path('/tmp/cantinho')))

    def test_credentials_are_not_sent_to_other_origins(self):
        with patch.object(self.client,'_request') as request:
            for url in ['https://other.example/a','//other.example/a','http://restaurant.example/a']:
                with self.subTest(url=url), self.assertRaises(ValueError): self.client.fetch(url)
            request.assert_not_called()

    def test_relative_assets_use_configured_server(self):
        with patch.object(self.client,'_request',return_value=b'asset') as request:
            self.assertEqual(b'asset',self.client.fetch('/api/signage/bundle/test/index.html'))
            request.assert_called_once_with('https://restaurant.example/api/signage/bundle/test/index.html')

    def test_authenticated_requests_never_follow_redirects(self):
        with self.assertRaises(ValueError):
            NoRedirect().redirect_request(None,None,302,'redirect',{},'https://other.example/')

    def test_player_watches_local_version_even_on_first_boot(self):
        body=with_version_watcher(b'<h1>Fallback</h1>',None)
        self.assertIn(b'const displayedVersion = null',body)
        self.assertIn(b"fetch('/version'",body)
        self.assertIn(b'location.reload()',body)
