#!/bin/bash

# Variables
BRANCH="master"
MAIN_VERSION=$1
BETA_VERSION="${MAIN_VERSION}-beta"

# Ensure we are on the latest master branch
git checkout $BRANCH
git pull origin $BRANCH

# Create a backup branch to prevent affecting master directly
BACKUP_BRANCH="temp-tagging-branch"
git checkout -b $BACKUP_BRANCH

# Update version in composer.json
echo "Updating composer.json with version $MAIN_VERSION"
sed -i "s/\"version\": \".*\"/\"version\": \"$MAIN_VERSION\"/" composer.json

# Update setup_version in module.xml
echo "Updating setup_version in module.xml"
sed -i "s/setup_version=\"[^\"]*\"/setup_version=\"$MAIN_VERSION\"/" etc/module.xml

# Commit and create a production tag
git add composer.json etc/module.xml
git commit -m "Release version $MAIN_VERSION"
git tag -a "$MAIN_VERSION" -m "Version $MAIN_VERSION"
echo "Production tag v$MAIN_VERSION created"

# Update to beta version in composer.json
echo "Updating composer.json with version $BETA_VERSION"
sed -i "s/\"version\": \"$MAIN_VERSION\"/\"version\": \"$BETA_VERSION\"/" composer.json

# Update setup_version in module.xml for beta version
echo "Updating setup_version in module.xml for beta"
sed -i "s/setup_version=\"$MAIN_VERSION\"/setup_version=\"$BETA_VERSION\"/" etc/module.xml

# Modify domain in init.phtml for beta
echo "Updating domain in init.phtml for beta"
sed -i 's/cdn.convertcart.com/cdn-beta.convertcart.com/' view/frontend/templates/init.phtml

# Commit and create a beta tag
git add composer.json etc/module.xml view/frontend/templates/init.phtml
git commit -m "Release beta version $BETA_VERSION"
git tag -a "$BETA_VERSION" -m "Beta version $BETA_VERSION"
echo "Beta tag $BETA_VERSION created"

# Push tags to remote
git push origin "$MAIN_VERSION"
git push origin "$BETA_VERSION"

# Checkout master and clean up the temporary branch
git checkout $BRANCH
git branch -D $BACKUP_BRANCH

echo "Tags $MAIN_VERSION and $BETA_VERSION are created and pushed."
