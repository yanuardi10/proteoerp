#!/bin/bash
# ProteoERP
# Script para importar data desde sucursales
cd /srv/www/htdocs/proteoerp
php index.php sincro importar gtraeshell $1 $2 $3
