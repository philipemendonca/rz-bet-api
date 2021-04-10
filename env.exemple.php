<?php

/**
 * Configurações de exibição de erros do PHP
 */
putenv('ERROR_REPORTING=' . E_ALL);
/**
 * Configurações de exibição de erros do Slim
 */
putenv('DISPLAY_ERRORS=1');
putenv('DISPLAY_ERRORS_DETAILS=' . true);

/**
 * Configurações de logs de erros
 */
putenv('LOG_ERROS=' . true);
putenv('LOG_ERROR_DETAILS=' . true);

/**
 * Configurações do MySQL
 */
putenv('MYSQL_HOST=localhost');
putenv('MYSQL_DBNAME=');
putenv('MYSQL_USER=');
putenv('MYSQL_PASSWORD=');

/**
 * Configuração da chave de tokens
 */
putenv('JWT_SECRET_KEY=');
