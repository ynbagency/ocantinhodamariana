import json

def with_version_watcher(body: bytes, version) -> bytes:
    """Watch the local cache only; WAN failure never interrupts rendering."""
    script = '''<script>
const displayedVersion = VERSION;
setInterval(async () => {
  try {
    const response = await fetch('/version', {cache: 'no-store'});
    if (!response.ok) return;
    const current = await response.json();
    if (current.content !== displayedVersion) location.reload();
  } catch (_) {}
}, 5000);
</script>'''.replace('VERSION', json.dumps(version))
    return body + script.encode('utf-8')
