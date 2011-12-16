#!/bin/bash
# ProteoERP
# Script para generar la contabilidad por shell
cd /srv/www/htdocs/proteoerp
php index.php contabilidad generar procesarshellmes $1 $2
