@echo off
echo Ejecutando verificacion del sistema de facturacion...
cd /d "c:\xampp\htdocs\Laravel\ProyectoClinica\clinicas"
php verify_billing_system.php
echo.
echo Presione cualquier tecla para continuar...
pause >nul
