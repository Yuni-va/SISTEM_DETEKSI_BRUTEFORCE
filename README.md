# Python Brute Force Worker

Folder ini berisi engine Python pendukung untuk sistem deteksi brute force.

## Install dependency

```bat
python -m pip install -r python\requirements.txt
```

## Jalankan monitor

```bat
python python\monitor.py
```

Atau dari Windows Explorer / terminal:

```bat
python\run_monitor.bat
```

## Jalankan sekali

```bat
python python\monitor.py --once
```

## Cek IP tertentu

```bat
python python\detector.py --username admin --ip 127.0.0.1
```

## Report ringkas

```bat
python python\report.py
python python\report.py --from-date 2026-06-01 --to-date 2026-06-30
```

Default koneksi database sama dengan aplikasi PHP:

- host: `localhost`
- user: `root`
- password: kosong
- database: `sistem_deteksi_bruteforce`

Nilai tersebut bisa dioverride dengan environment variable `DB_HOST`, `DB_USER`, `DB_PASS`, dan `DB_NAME`.
