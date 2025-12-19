# Hamnaghseh PM - Custom Capabilities

## Available Capability

### Admin Capability
- `hamnaghsheh_admin` - **ONE capability for full access** to all plugin features
  - View any project
  - Upload to any project
  - Manage all orders
  - Set prices
  - Create projects

## How to Assign Capability

### Method 1: User Role Editor Plugin (Recommended)
1. Install "User Role Editor" plugin
2. Go to Users → User Role Editor
3. Select the role (Administrator, Editor, or custom role)
4. Check ✅ `hamnaghsheh_admin`
5. Click "Update"

### Method 2: Programmatically
```php
// Grant to specific user
$user = get_user_by('id', 20);
if ($user) {
    $user->add_cap('hamnaghsheh_admin');
}

// Grant to a role
$role = get_role('editor');
if ($role) {
    $role->add_cap('hamnaghsheh_admin');
}
```

### Method 3: Create Custom Role
```php
add_role('hamnaghsheh_manager', 'Hamnaghsheh Manager', array(
    'read' => true,
    'hamnaghsheh_admin' => true
));
```

## Default Assignments

After plugin activation, Administrators automatically receive the `hamnaghsheh_admin` capability.

## Benefits

✅ **Simple** - Just ONE capability instead of six  
✅ **Clear** - Either admin or not, no confusion  
✅ **Easy to grant** - One checkbox for full access  
✅ **Scalable** - Can create custom roles later  

## How It Works

### Project Access Control

When a user tries to access a project, the system checks in this order:

1. **Capability Check**: Does the user have `hamnaghsheh_admin` capability?
   - If YES → Grant access
   - If NO → Continue to next check

2. **Owner Check**: Is the user the project owner?
   - If YES → Grant access
   - If NO → Continue to next check

3. **Assignment Check**: Is the user assigned to the project?
   - If YES → Grant access
   - If NO → Deny access

### Upload Permission Control

When a user tries to upload files to a project, the system checks in this order:

1. **Capability Check**: Does the user have `hamnaghsheh_admin` capability?
   - If YES → Allow upload
   - If NO → Continue to next check

2. **Owner Check**: Is the user the project owner?
   - If YES → Allow upload
   - If NO → Continue to next check

3. **Assignment Check**: Is the user assigned with 'upload' permission?
   - If YES → Allow upload
   - If NO → Deny upload

### Order Management

Order management pages and AJAX handlers check for the `hamnaghsheh_admin` capability:

- Viewing order list: Requires `hamnaghsheh_admin`
- Setting prices: Requires `hamnaghsheh_admin`
- Updating status: Requires `hamnaghsheh_admin`
- Creating projects from orders: Requires `hamnaghsheh_admin`

## Use Cases

### Admin Can Access All Projects
**Problem**: When an admin creates a project from an order, the customer becomes the owner, but the admin cannot access it.

**Solution**: Admins automatically have the `hamnaghsheh_admin` capability, so they can:
- View any project in the system
- Upload files to any project
- Manage projects created from orders
- Set order prices
- Manage all orders

### Multiple Administrators
**Use Case**: You want to add a second administrator who can manage orders.

**Solution**: 
1. Create a new user account
2. Assign the "Administrator" role
3. They automatically get the `hamnaghsheh_admin` capability upon plugin activation

### Project Manager Role
**Use Case**: You want a "Project Manager" who can manage all projects and orders.

**Solution**:
```php
// Add this code to your theme's functions.php or a custom plugin
add_role('hamnaghsheh_manager', 'Hamnaghsheh Manager', array(
    'read' => true,
    'hamnaghsheh_admin' => true
));
```

## Security Considerations

1. **Least Privilege Principle**: Only grant the `hamnaghsheh_admin` capability to users who need full access
2. **Regular Audits**: Periodically review which users have the `hamnaghsheh_admin` capability
3. **Testing**: Always test capabilities in a staging environment before applying to production
4. **Specific Access**: If you need more granular control in the future, consider extending the system with additional capabilities

## Troubleshooting

### Capabilities Not Working After Activation
**Solution**: Deactivate and reactivate the plugin to trigger capability assignment.

### User Still Can't Access Projects
**Check**:
1. Is the user logged in?
2. Does the user have the `hamnaghsheh_admin` capability?
3. If not admin, is the user the owner or assigned to the project?

### Admin Menu Not Showing
**Check**: Ensure the user has the `hamnaghsheh_admin` capability.

## Migration Notes

For existing installations:
- Existing project owners maintain their access via the `user_id` field
- Existing assigned users maintain their access via the assignments table
- New capability layer adds administrator access on top of existing permissions
- No database changes needed - this is purely capability-based access control
