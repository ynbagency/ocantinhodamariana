from dataclasses import dataclass
from pathlib import Path
import json, os

@dataclass(frozen=True)
class Config:
    server_url: str
    device_id: str
    device_token: str
    data_dir: Path
    sync_seconds: int = 60
    port: int = 8765

    @classmethod
    def load(cls, path=None):
        path = Path(path or os.environ.get("CANTINHO_CONFIG", "/etc/cantinho/device.json"))
        raw = json.loads(path.read_text(encoding="utf-8"))
        return cls(
            server_url=raw["server_url"].rstrip("/"), device_id=raw["device_id"],
            device_token=raw["device_token"], data_dir=Path(raw.get("data_dir","/var/lib/cantinho")),
            sync_seconds=int(raw.get("sync_seconds",60)), port=int(raw.get("port",8765)))
