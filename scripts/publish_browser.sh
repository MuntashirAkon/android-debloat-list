#!/usr/bin/env sh

# Generate MD files
php scripts/browser_generator.php

# Build website
cd browser
mdbook build

# Publish
cd book
git init
git add .
git commit
git push -f git@github.com:MuntashirAkon/android-debloat-list.git master:site
