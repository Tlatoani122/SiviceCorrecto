@echo off
title Iniciando SIVICE...

echo ================================
echo Iniciando XAMPP...
echo ================================

start "" "C:\xampp\xampp-control.exe"

timeout /t 5

echo ================================
echo Iniciando Backend Laravel...
echo ================================

start cmd /k "cd /d C:\xampp\htdocs\SIVICE\backend && php artisan serve"

timeout /t 3

echo ================================
echo Iniciando Frontend Vue...
echo ================================

:: Se asume que la carpeta frontend también está dentro de SIVICE en htdocs
start cmd /k "cd /d C:\xampp\htdocs\SIVICE\frontend && npm run dev"

timeout /t 5

echo ================================
echo Abriendo VS Code...
echo ================================

start "" "C:\Users\2949665\AppData\Local\Programs\Microsoft VS Code\Code.exe" "C:\xampp\htdocs\SIVICE\siviceVSC.code-workspace"

timeout /t 5

echo ================================
echo Abriendo navegador...
echo ================================

start "" "http://localhost:5173"

echo ================================
echo SIVICE listo 🚀
echo ================================
pause