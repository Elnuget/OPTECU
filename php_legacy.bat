@echo off
REM Script para configurar OpenSSL legacy y ejecutar PHP

echo Configurando OpenSSL para certificados legacy...
set OPENSSL_CONF=%~dp0openssl_legacy.cnf

echo Ejecutando script PHP...
php %*
