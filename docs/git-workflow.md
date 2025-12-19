# Alur Git Aman untuk `main`

- Periksa status: `git status`
- Jika tertinggal dari remote: `git pull --rebase`
- Jika ada perubahan lokal:
  - Komit lalu rebase: `git commit -m "Update deploy workflow"` kemudian `git pull --rebase`
  - Atau simpan sementara: `git stash --include-untracked` → `git pull` → `git stash pop`
- Jika ingin membatalkan perubahan yang di-stage:
  - `git restore --staged .github/workflows/deploy.yml`
  - `git checkout -- .github/workflows/deploy.yml`
- Setelah sinkron dan konflik selesai: `git push`

