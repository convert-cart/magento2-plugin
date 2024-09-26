#!/bin/bash

# Variables
BRANCH="master"
MAIN_VERSION=$1
BETA_VERSION="${MAIN_VERSION}-beta"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Exit the script if any command fails
set -e

# Function to handle errors (POSIX compliant)
handle_error() {
    printf "${RED}Error occurred: %s${NC}\n" "$1"
    cleanup
    exit 1
}

# Cleanup function
cleanup() {
    # Checkout master branch
    git checkout $BRANCH || printf "${RED}Failed to checkout $BRANCH during cleanup.${NC}\n"

    # Delete the temporary branch if it exists
    if git show-ref --verify --quiet refs/heads/temp-tagging-branch; then
        git branch -D temp-tagging-branch || printf "${RED}Failed to delete temporary branch temp-tagging-branch.${NC}\n"
    fi

    # Remove any created tags if necessary
    if git tag -l | grep -q "$MAIN_VERSION"; then
        git tag -d "$MAIN_VERSION" || printf "${RED}Failed to delete tag $MAIN_VERSION.${NC}\n"
    fi
    if git tag -l | grep -q "$BETA_VERSION"; then
        git tag -d "$BETA_VERSION" || printf "${RED}Failed to delete tag $BETA_VERSION.${NC}\n"
    fi
}

# Ensure version number is provided
if [ -z "$MAIN_VERSION" ]; then
    handle_error "Please provide a version number. Usage: ./tagger.sh VERSION_NUMBER"
fi

# Ensure we are on the latest master branch
printf "${YELLOW}Checking out the latest master branch...${NC}\n"
git checkout $BRANCH || handle_error "Failed to checkout $BRANCH"
git pull origin $BRANCH || handle_error "Failed to pull latest changes from $BRANCH"

# Create a backup branch to prevent affecting master directly
BACKUP_BRANCH="temp-tagging-branch"
git checkout -b $BACKUP_BRANCH || handle_error "Failed to create temporary branch $BACKUP_BRANCH"

# Remove tagger.sh from staging so it doesn't get tagged
git rm --cached tagger.sh || handle_error "Failed to remove tagger.sh from staging"

# Update version in composer.json
printf "${YELLOW}Updating composer.json with version %s...${NC}\n" "$MAIN_VERSION"
sed -i "s/\"version\": \".*\"/\"version\": \"$MAIN_VERSION\"/" composer.json || handle_error "Failed to update composer.json"

# Update setup_version in module.xml
printf "${YELLOW}Updating setup_version in module.xml...${NC}\n"
sed -i "s/setup_version=\"[^\"]*\"/setup_version=\"$MAIN_VERSION\"/" etc/module.xml || handle_error "Failed to update module.xml"

# Commit and create a production tag
git add composer.json etc/module.xml || handle_error "Failed to add files for commit"
git commit -m "Release version $MAIN_VERSION" || handle_error "Failed to commit changes"
git tag -a "$MAIN_VERSION" -m "Version $MAIN_VERSION" || handle_error "Failed to create production tag"
printf "${GREEN}Production tag %s created successfully${NC}\n" "$MAIN_VERSION"

# Update to beta version in composer.json
printf "${YELLOW}Updating composer.json with version %s...${NC}\n" "$BETA_VERSION"
sed -i "s/\"version\": \"$MAIN_VERSION\"/\"version\": \"$BETA_VERSION\"/" composer.json || handle_error "Failed to update composer.json for beta"

# Update setup_version in module.xml for beta version
printf "${YELLOW}Updating setup_version in module.xml for beta version...${NC}\n"
sed -i "s/setup_version=\"$MAIN_VERSION\"/setup_version=\"$BETA_VERSION\"/" etc/module.xml || handle_error "Failed to update module.xml for beta"

# Modify domain in init.phtml for beta
printf "${YELLOW}Updating domain in init.phtml for beta...${NC}\n"
sed -i 's/cdn.convertcart.com/cdn-beta.convertcart.com/' view/frontend/templates/init.phtml || handle_error "Failed to update domain in init.phtml for beta"

# Commit and create a beta tag
git add composer.json etc/module.xml view/frontend/templates/init.phtml || handle_error "Failed to add files for beta commit"
git commit -m "Release beta version $BETA_VERSION" || handle_error "Failed to commit beta version"
git tag -a "$BETA_VERSION" -m "Beta version $BETA_VERSION" || handle_error "Failed to create beta tag"
printf "${GREEN}Beta tag %s created successfully${NC}\n" "$BETA_VERSION"

# Push tags to remote
printf "${YELLOW}Pushing tags to remote...${NC}\n"
git push origin "$MAIN_VERSION" || handle_error "Failed to push production tag"
git push origin "$BETA_VERSION" || handle_error "Failed to push beta tag"
printf "${GREEN}Tags %s and %s pushed to remote successfully${NC}\n" "$MAIN_VERSION" "$BETA_VERSION"

# Final cleanup: Checkout master and clean up the temporary branch
cleanup
printf "${GREEN}Tags %s and %s are created, pushed, and tagger.sh remains in the branch.${NC}\n" "$MAIN_VERSION" "$BETA_VERSION"
