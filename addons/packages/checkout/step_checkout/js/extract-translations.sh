#!/bin/bash

output_file="./src/locale/translations.json"
temp_file="temp_translations.txt"

# Find all .vue files and extract strings
find ./src/ -type f -name "*.vue" -print0 | while IFS= read -r -d '' file; do
    grep -oP "(?<=\$t\(').*?(?='\))|(?<=t\(').*?(?='\))" "$file"
done | sort -u > "$temp_file"

# Create the JavaScript object
echo "{" > "$output_file"
while IFS= read -r line; do
    echo "  \"$line\": \"$line\"," >> "$output_file"
done < "$temp_file"

# Remove the trailing comma and close the object
sed -i '$ s/,$//' "$output_file"
echo "}" >> "$output_file"

# Remove the temporary file
rm "$temp_file"