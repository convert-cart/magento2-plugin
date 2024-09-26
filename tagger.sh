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
    printf "${YELLOW}Checking out master branch during cleanup...${NC}\n"
    git checkout $BRANCH || printf "${RED}Failed to checkout $BRANCH during cleanup.${NC}\n"

    # Delete the temporary branch if it exists
    if git show-ref --verify --quiet refs/heads/temp-tagging-branch; then
        git branch -D temp-tagging-branch || printf "${RED}Failed to delete temporary branch temp-tagging-branch.${NC}\n"
    fi
}

# Ensure version number is provided
if [ -z "$MAIN_VERSION" ]; then
    handle_error "Please provide a version number. Usage: ./tagger.sh VERSION_NUMBER"
fi

# Prompt for tag creation options
echo "Select the tags you want to create:"
echo "1) Production tag"
echo "2) Beta tag"
echo "3) Both"
echo -n "Enter your choice (1/2/3): "
read -r choice

# Ensure we are on the latest master branch
printf "${YELLOW}Checking out the latest master branch...${NC}\n"
git checkout $BRANCH || handle_error "Failed to checkout $BRANCH"
git pull origin $BRANCH || handle_error "Failed to pull latest changes from $BRANCH"

# Create a backup branch to prevent affecting master directly
BACKUP_BRANCH="temp-tagging-branch"
git checkout -b $BACKUP_BRANCH || handle_error "Failed to create temporary branch $BACKUP_BRANCH"

# Function to check if a tag exists
check_tag_exists() {
    git tag -l | grep -q "$1"
}

# Function to confirm tag deletion
confirm_tag_deletion() {
    echo -n "${YELLOW}Tag '$1' already exists. Do you want to delete it and recreate it? (y/n): ${NC}"
    read -r response
    if [[ "$response" != "y" ]]; then
        printf "${YELLOW}Keeping existing tag '%s'. Skipping creation...${NC}\n" "$1"
        return 1
    fi
    return 0
}

# Update version in composer.json
printf "${YELLOW}Updating composer.json with version %s...${NC}\n" "$MAIN_VERSION"
sed -i "s/\"version\": \".*\"/\"version\": \"$MAIN_VERSION\"/" composer.json || handle_error "Failed to update composer.json"

# Update setup_version in module.xml
printf "${YELLOW}Updating setup_version in module.xml...${NC}\n"
sed -i "s/setup_version=\"[^\"]*\"/setup_version=\"$MAIN_VERSION\"/" etc/module.xml || handle_error "Failed to update module.xml"

# Commit changes for production tag if chosen
if [[ "$choice" == "1" || "$choice" == "3" ]]; then
    if check_tag_exists "$MAIN_VERSION"; then
        if confirm_tag_deletion "$MAIN_VERSION"; then
            git tag -d "$MAIN_VERSION" || handle_error "Failed to delete existing production tag"
        else
            printf "${GREEN}Skipping creation of production tag %s.${NC}\n" "$MAIN_VERSION"
        fi
    fi
    
    # Commit and create a production tag
    git add composer.json etc/module.xml || handle_error "Failed to add files for production commit"
    git commit -m "Release version $MAIN_VERSION" || handle_error "Failed to commit production changes"
    git tag -a "$MAIN_VERSION" -m "Version $MAIN_VERSION" || handle_error "Failed to create production tag"
    printf "${GREEN}Production tag %s created successfully${NC}\n" "$MAIN_VERSION"
fi

# Update to beta version in composer.json
printf "${YELLOW}Updating composer.json with version %s...${NC}\n" "$BETA_VERSION"
sed -i "s/\"version\": \"$MAIN_VERSION\"/\"version\": \"$BETA_VERSION\"/" composer.json || handle_error "Failed to update composer.json for beta"

# Update setup_version in module.xml for beta version
printf "${YELLOW}Updating setup_version in module.xml for beta version...${NC}\n"
sed -i "s/setup_version=\"$MAIN_VERSION\"/setup_version=\"$BETA_VERSION\"/" etc/module.xml || handle_error "Failed to update module.xml for beta"

# Modify domain in init.phtml for beta
printf "${YELLOW}Updating domain in init.phtml for beta...${NC}\n"
sed -i 's/cdn.convertcart.com/cdn-beta.convertcart.com/' view/frontend/templates/init.phtml || handle_error "Failed to update domain in init.phtml for beta"

# Commit changes for beta tag if chosen
if [[ "$choice" == "2" || "$choice" == "3" ]]; then
    if check_tag_exists "$BETA_VERSION"; then
        if confirm_tag_deletion "$BETA_VERSION"; then
            git tag -d "$BETA_VERSION" || handle_error "Failed to delete existing beta tag"
        else
            printf "${GREEN}Skipping creation of beta tag %s.${NC}\n" "$BETA_VERSION"
        fi
    fi
    
    # Commit and create a beta tag
    git add composer.json etc/module.xml view/frontend/templates/init.phtml || handle_error "Failed to add files for beta commit"
    git commit -m "Release beta version $BETA_VERSION" || handle_error "Failed to commit beta version"
    git tag -a "$BETA_VERSION" -m "Beta version $BETA_VERSION" || handle_error "Failed to create beta tag"
    printf "${GREEN}Beta tag %s created successfully${NC}\n" "$BETA_VERSION"
fi

# Push tags to remote if created
if [[ "$choice" == "1" || "$choice" == "3" ]]; then
    printf "${YELLOW}Pushing production tag to remote...${NC}\n"
    git push -f origin "$MAIN_VERSION" || handle_error "Failed to push production tag"
fi

if [[ "$choice" == "2" || "$choice" == "3" ]]; then
    printf "${YELLOW}Pushing beta tag to remote...${NC}\n"
    git push -f origin "$BETA_VERSION" || handle_error "Failed to push beta tag"
fi

# Final cleanup: Checkout master and clean up the temporary branch
cleanup
printf "${GREEN}Tags processing completed.${NC}\n"
