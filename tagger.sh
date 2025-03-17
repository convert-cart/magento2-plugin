#!/bin/bash

# Variables
BRANCH="master"
MAIN_VERSION=$1
BETA_VERSION="${MAIN_VERSION}-beta"
BACKUP_BRANCH="temp-tagging-branch"  # Define the backup branch name

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
    # Save the current exit status
    local exit_status=$?
    
    # Don't exit on errors during cleanup
    set +e
    
    printf "${YELLOW}Performing cleanup...${NC}\n"
    
    git reset .
    if [ $? -ne 0 ]; then
        printf "${RED}Failed to reset git changes: $?${NC}\n"
    fi
    
    git clean -fd .
    if [ $? -ne 0 ]; then
        printf "${RED}Failed to clean git directory: $?${NC}\n"
    fi
    
    git checkout .
    if [ $? -ne 0 ]; then
        printf "${RED}Failed to checkout changes: $?${NC}\n"
    fi

    # Checkout master branch
    printf "${YELLOW}Checking out master branch during cleanup...${NC}\n"
    git checkout $BRANCH
    if [ $? -ne 0 ]; then
        printf "${RED}Failed to checkout $BRANCH during cleanup: $?${NC}\n"
    fi

    # Delete the temporary branch if it exists
    if git show-ref --verify --quiet refs/heads/$BACKUP_BRANCH; then
        git branch -D $BACKUP_BRANCH
        if [ $? -ne 0 ]; then
            printf "${RED}Failed to delete temporary branch $BACKUP_BRANCH: $?${NC}\n"
        fi
    fi
    
    # Restore the exit status
    return $exit_status
}

# Function definitions
check_remote_tag_exists() {
    git fetch --tags
    git ls-remote --tags origin | grep -q "refs/tags/$1"
}

get_tag_creation_date() {
    git log -1 --format=%aD "$1" 2>/dev/null
}

confirm_tag_deletion() {
    echo -n "${YELLOW}Tag '$1' already exists on remote (created on: $2). \nDo you want to delete it and recreate it? (y/n): ${NC}"
    read response
    if [ "$response" != "y" ]; then
        printf "${YELLOW}Keeping existing tag '%s'. Skipping creation...${NC}\n" "$1"
        return 1
    fi
    return 0
}

check_file_exists() {
    if [ ! -f "$1" ]; then
        handle_error "File $1 not found!"
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
read choice

# Ensure we are on the latest master branch
printf "${YELLOW}Checking out the latest master branch...${NC}\n"
git checkout $BRANCH || handle_error "Failed to checkout $BRANCH"
git pull origin $BRANCH || handle_error "Failed to pull latest changes from $BRANCH"

# Check if old backup branch exists and remove it
if git show-ref --verify --quiet refs/heads/$BACKUP_BRANCH; then
    printf "${YELLOW}Old backup branch '$BACKUP_BRANCH' found. Deleting it...${NC}\n"
    git branch -D $BACKUP_BRANCH || handle_error "Failed to delete old backup branch"
fi

# Create a backup branch to prevent affecting master directly
printf "${YELLOW}Attempting to create backup branch...${NC}\n"
if ! git checkout -b $BACKUP_BRANCH; then
    handle_error "Failed to create temporary branch $BACKUP_BRANCH"
fi
printf "${GREEN}Successfully created and switched to backup branch${NC}\n"

# Verify we're on the correct branch
current_branch=$(git branch --show-current)
if [ "$current_branch" != "$BACKUP_BRANCH" ]; then
    handle_error "Failed to switch to backup branch. Current branch: $current_branch"
fi

# Check if files exist before proceeding
printf "${YELLOW}Verifying required files...${NC}\n"
for file in "composer.json" "etc/module.xml" "view/frontend/templates/init.phtml"; do
    if [ ! -f "$file" ]; then
        handle_error "Required file not found: $file"
    fi
    printf "${GREEN}Found required file: $file${NC}\n"
done

# Try to read composer.json to verify file access
if ! cat composer.json > /dev/null 2>&1; then
    handle_error "Cannot read composer.json file"
fi

printf "${YELLOW}Proceeding with version updates...${NC}\n"

# Check for existing remote tags
if [ "$choice" = "1" ] || [ "$choice" = "3" ]; then
    if check_remote_tag_exists "$MAIN_VERSION"; then
        creation_date=$(get_tag_creation_date "$MAIN_VERSION")
        if confirm_tag_deletion "$MAIN_VERSION" "$creation_date"; then
            git tag -d "$MAIN_VERSION" || handle_error "Failed to delete existing production tag"
        else
            printf "${GREEN}Skipping creation of production tag %s.${NC}\n" "$MAIN_VERSION"
        fi
    fi
fi

if [ "$choice" = "2" ] || [ "$choice" = "3" ]; then
    if check_remote_tag_exists "$BETA_VERSION"; then
        creation_date=$(get_tag_creation_date "$BETA_VERSION")
        if confirm_tag_deletion "$BETA_VERSION" "$creation_date"; then
            git tag -d "$BETA_VERSION" || handle_error "Failed to delete existing beta tag"
        else
            printf "${GREEN}Skipping creation of beta tag %s.${NC}\n" "$BETA_VERSION"
        fi
    fi
fi

# Check if required files exist before proceeding
check_file_exists "composer.json"
check_file_exists "etc/module.xml"
check_file_exists "view/frontend/templates/init.phtml"

# Update version in composer.json
printf "${YELLOW}Updating composer.json with version %s...${NC}\n" "$MAIN_VERSION"
if ! sed -i "s/\"version\": \".*\"/\"version\": \"$MAIN_VERSION\"/" composer.json; then
    handle_error "Failed to update composer.json"
fi

# Update setup_version in module.xml
printf "${YELLOW}Updating setup_version in module.xml...${NC}\n"
if ! sed -i "s/setup_version=\"[^\"]*\"/setup_version=\"$MAIN_VERSION\"/" etc/module.xml; then
    handle_error "Failed to update module.xml"
fi

# Commit changes for production tag if chosen
if [ "$choice" = "1" ] || [ "$choice" = "3" ]; then
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
if ! sed -i 's/cdn\.convertcart\.com/cdn-beta.convertcart.com/' view/frontend/templates/init.phtml; then
    handle_error "Failed to update domain in init.phtml for beta"
fi

# Commit changes for beta tag if chosen
if [ "$choice" = "2" ] || [ "$choice" = "3" ]; then
    git add composer.json etc/module.xml view/frontend/templates/init.phtml || handle_error "Failed to add files for beta commit"
    git commit -m "Release beta version $BETA_VERSION" || handle_error "Failed to commit beta version"
    git tag -a "$BETA_VERSION" -m "Beta version $BETA_VERSION" || handle_error "Failed to create beta tag"
    printf "${GREEN}Beta tag %s created successfully${NC}\n" "$BETA_VERSION"
fi

# Push tags to remote if created
if [ "$choice" = "1" ] || [ "$choice" = "3" ]; then
    printf "${YELLOW}Pushing production tag to remote...${NC}\n"
    git push -f origin "$MAIN_VERSION" || handle_error "Failed to push production tag"
fi

if [ "$choice" = "2" ] || [ "$choice" = "3" ]; then
    printf "${YELLOW}Pushing beta tag to remote...${NC}\n"
    git push -f origin "$BETA_VERSION" || handle_error "Failed to push beta tag"
fi

# Final cleanup: Checkout master and clean up the temporary branch
cleanup
printf "${GREEN}Tags processing completed.${NC}\n"

# Trap ERR signal to ensure cleanup runs even on unexpected errors
trap 'handle_error "Unexpected error occurred"' ERR
