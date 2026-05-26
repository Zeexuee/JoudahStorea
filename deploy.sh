#!/bin/bash

echo "=== JoudahStore Deployment Script ==="
echo ""

cd domains/joudahstore.com

echo "=== Current Branch ==="
git branch
echo ""

echo "=== Git Status ==="
git status
echo ""

echo "=== Checkout to F-/3/2/26/o/-No-Error branch ==="
git checkout F-/3/2/26/o/-No-Error
echo ""

echo "=== Pulling latest code ==="
git pull origin F-/3/2/26/o/-No-Error
echo ""

echo "=== Running migrations ==="
php artisan migrate --force
echo ""

echo "=== Clearing cache ==="
php artisan cache:clear
php artisan config:clear
echo ""

echo "=== Optimize ==="
php artisan optimize:clear
echo ""

echo "✅ Deployment complete!"
