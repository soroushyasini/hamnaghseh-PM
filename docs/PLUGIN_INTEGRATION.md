# Plugin Integration Guide

This document describes how to integrate with Hamnaghsheh PM plugin.

## Available Hooks

### Actions

#### `hamnaghsheh_file_action`
Fires when a file action occurs (upload, replace, delete, download, see).

**Parameters:**
- `$file_id` (int) - ID of the file
- `$project_id` (int) - ID of the project
- `$user_id` (int) - ID of the user who performed the action
- `$action_type` (string) - Type of action: 'upload', 'replace', 'delete', 'download', 'see'

**Example:**
```php
add_action('hamnaghsheh_file_action', 'my_custom_handler', 10, 4);

function my_custom_handler($file_id, $project_id, $user_id, $action_type) {
    // Your custom logic here
    error_log("User $user_id performed $action_type on file $file_id");
}
```

#### `hamnaghsheh_chat_render`
Fires on project page, allowing plugins to inject UI (e.g., chat box).

**Parameters:**
- `$project_id` (int) - ID of the current project
- `$project` (object) - Full project object

**Example:**
```php
add_action('hamnaghsheh_chat_render', 'render_my_ui', 10, 2);

function render_my_ui($project_id, $project) {
    echo '<div class="my-chat-widget">Chat interface here</div>';
}
```

## Available Helper Functions

### File Operations

#### `Hamnaghsheh_File_Upload::get_file_by_id($file_id)`
Returns file object by ID.

**Parameters:**
- `$file_id` (int) - The file ID

**Returns:**
- `object|null` - File object with properties (id, file_name, file_size, file_path, etc.) or null if not found

**Example:**
```php
$file = Hamnaghsheh_File_Upload::get_file_by_id(123);
if ($file) {
    echo "File name: " . $file->file_name;
}
```

#### `Hamnaghsheh_File_Upload::get_project_files($project_id)`
Returns array of all files in a project.

**Parameters:**
- `$project_id` (int) - The project ID

**Returns:**
- `array` - Array of file objects

**Example:**
```php
$files = Hamnaghsheh_File_Upload::get_project_files(456);
foreach ($files as $file) {
    echo "File: " . $file->file_name . "<br>";
}
```

### Project Operations

#### `Hamnaghsheh_Projects::get_user_project_permission($project_id, $user_id)`
Returns user's permission level: 'owner', 'upload', 'view', or false.

**Parameters:**
- `$project_id` (int) - The project ID
- `$user_id` (int|null) - User ID (optional, defaults to current user)

**Returns:**
- `string|false` - Permission level ('owner', 'upload', 'view') or false if no access

**Example:**
```php
$permission = Hamnaghsheh_Projects::get_user_project_permission(456);
if ($permission === 'owner') {
    echo "You own this project";
} elseif ($permission === 'upload') {
    echo "You can upload files";
} elseif ($permission === 'view') {
    echo "You can only view files";
} else {
    echo "No access";
}
```

#### `Hamnaghsheh_Projects::get_project_by_id($project_id)`
Returns project object by ID.

**Parameters:**
- `$project_id` (int) - The project ID

**Returns:**
- `object|null` - Project object or null if not found

**Example:**
```php
$project = Hamnaghsheh_Projects::get_project_by_id(456);
if ($project) {
    echo "Project name: " . $project->name;
}
```

## Integration Example (Chat Plugin)

See [hamnaghsheh-messenger](https://github.com/soroushyasini/hamnaghsheh-messenger) for a complete example of how to integrate with this plugin.

### Basic Integration Steps

1. **Hook into file actions** to capture and display activity:
```php
add_action('hamnaghsheh_file_action', 'my_chat_log_action', 10, 4);

function my_chat_log_action($file_id, $project_id, $user_id, $action_type) {
    // Store the action in your chat/activity log
    $file = Hamnaghsheh_File_Upload::get_file_by_id($file_id);
    $message = sprintf(
        'User %d %s file "%s" in project %d',
        $user_id,
        $action_type,
        $file ? $file->file_name : 'unknown',
        $project_id
    );
    // Save to your database or send notification
}
```

2. **Display your UI on project pages**:
```php
add_action('hamnaghsheh_chat_render', 'my_chat_render_ui', 10, 2);

function my_chat_render_ui($project_id, $project) {
    // Check if user has access
    $permission = Hamnaghsheh_Projects::get_user_project_permission($project_id);
    if (!$permission) {
        return; // No access
    }
    
    // Render your chat interface
    ?>
    <div class="my-chat-container">
        <h3>Project Chat</h3>
        <div class="chat-messages" data-project-id="<?php echo esc_attr($project_id); ?>">
            <!-- Your chat messages here -->
        </div>
    </div>
    <?php
}
```

3. **Use helper functions** to enhance functionality:
```php
// Get project files for autocomplete
$files = Hamnaghsheh_File_Upload::get_project_files($project_id);

// Check user permissions before allowing actions
$permission = Hamnaghsheh_Projects::get_user_project_permission($project_id, $user_id);
if ($permission !== 'owner') {
    wp_die('Only owners can perform this action');
}
```

## Security Considerations

- All hooks are output-escaped (chat plugin's responsibility)
- Helper functions use `$wpdb->prepare()` for SQL safety
- Permission checks are enforced before exposing data
- Always validate user input in your integration code
- Check user permissions before displaying sensitive information

## Backward Compatibility

- All integration features are non-breaking additions
- Hooks only execute if other plugins listen to them (zero overhead if not)
- Main plugin works independently (with or without chat plugin)
- Helper functions return null/false when data not found

## Support

For issues or questions about integration, please:
1. Check the [hamnaghsheh-messenger](https://github.com/soroushyasini/hamnaghsheh-messenger) reference implementation
2. Open an issue in the [Hamnaghsheh PM repository](https://github.com/soroushyasini/hamnaghseh-PM)
