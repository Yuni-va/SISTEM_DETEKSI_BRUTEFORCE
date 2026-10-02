@echo off
cd /d "%~dp0\.."
python -m pip install -r python\requirements.txt
python python\monitor.py
