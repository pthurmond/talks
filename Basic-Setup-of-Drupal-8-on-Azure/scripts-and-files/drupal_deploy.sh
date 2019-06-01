#!/usr/bin/env bash

# ====================================================
# Deploy Drupal specific operations
# ====================================================

# Check for mysql client before running Drush
type mysql >/dev/null 2>&1 && echo "MySQL present." || apt-get install mysql-client -y

# Put the site into maintenance mode
./vendor/bin/drush sset system.maintenance_mode 1 --strict=0

# Check for DB updates
./vendor/bin/drush updb -y --strict=0



# --------------------------------------------------------------------
# Currently, I have entity updates and config sync disabled because
# I am unsure how out of sync this website is. Test locally first.
# --------------------------------------------------------------------

# Run the Entity updates
# ./vendor/bin/drush entup -y --strict=0

# Config sync
# ./vendor/bin/drush cim sync -y --strict=0



# Clear cache
./vendor/bin/drush cr

# Bring it back out of maintenance mode
./vendor/bin/drush sset system.maintenance_mode 0 --strict=0
