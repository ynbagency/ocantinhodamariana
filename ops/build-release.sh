#!/usr/bin/env bash
set -euo pipefail
root="$(cd "$(dirname "$0")/.." && pwd)"; out="${1:-$root/dist}"; mkdir -p "$out"
name="cantinho-$(git -C "$root" rev-parse --short HEAD).tar.gz"
tar -C "$root" --exclude=.git --exclude=.env --exclude=dist --exclude='storage/logs/*' -czf "$out/$name" .
sha256sum "$out/$name" > "$out/$name.sha256"
echo "$out/$name"
