@echo off
title Iniciar SIVICE
color 0A

echo ============================================
echo   INICIANDO SIVICE
echo ============================================

echo Cerrando procesos anteriores...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1

echo Deteniendo MariaDB externo si existe...
net stop MariaDB >nul 2>&1

echo Abriendo XAMPP...
start "" "C:\xampp\xampp-control.exe"

echo Esperando XAMPP...
timeout /t 6 /nobreak >nul

echo Verificando puerto MySQL 3306...
netstat -ano | findstr :3306

echo.
echo Iniciando Laravel...
start "SIVICE Backend Laravel" cmd /k "cd /d C:\xampp\htdocs\SIVICE\backend && php artisan optimize:clear && php artisan serve --host=127.0.0.1 --port=8000"

timeout /t 6 /nobreak >nul

echo.
echo Iniciando Vue...
start "SIVICE Frontend Vue" cmd /k "cd /d C:\xampp\htdocs\SIVICE\frontend && npm run dev"

timeout /t 8 /nobreak >nul

echo.
echo Abriendo Visual Studio Code...
start "" "C:\Users\2949665\AppData\Local\Programs\Microsoft VS Code\Code.exe" "C:\xampp\htdocs\SIVICE"

echo.
echo Abriendo SIVICE...
start "" "http://localhost:5173/login"

echo.
echo SISTEMA INICIADO.
pause