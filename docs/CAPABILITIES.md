# Hamnaghseh PM - Custom Capabilities

## Available Capabilities

### Project Capabilities
- `view_all_projects` - View any project without being assigned
- `manage_projects` - Create, edit, and delete projects
- `upload_to_any_project` - Upload files to any project

### Order Capabilities
- `view_all_orders` - View all orders in admin panel
- `manage_orders` - Edit order details and manage workflow
- `set_order_prices` - Set final prices and change order status

## How to Assign Capabilities

### Method 1: User Role Editor Plugin (Recommended)
1. Install "User Role Editor" plugin
2. Go to Users → User Role Editor
3. Select the role (Administrator, Editor, or custom role)
4. Check the desired capabilities
5. Click "Update"

### Method 2: Programmatically
```php
// Assign to specific user
$user = get_user_by('id', 20);
$user->add_cap('view_all_projects');

// Assign to a role
$role = get_role('editor');
$role->add_cap('view_all_projects');
```

### Method 3: Create Custom Role
```php
add_role('project_manager', 'Project Manager', array(
    'read' => true,
    'view_all_projects' => true,
    'manage_projects' => true,
    'upload_to_any_project' => true
));
```

## Default Assignments

After plugin activation, Administrators automatically receive all capabilities.

## How It Works

### Project Access Control

When a user tries to access a project, the system checks in this order:

1. **Capability Check**: Does the user have `view_all_projects` capability?
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

1. **Capability Check**: Does the user have `upload_to_any_project` capability?
   - If YES → Allow upload
   - If NO → Continue to next check

2. **Owner Check**: Is the user the project owner?
   - If YES → Allow upload
   - If NO → Continue to next check

3. **Assignment Check**: Is the user assigned with 'upload' permission?
   - If YES → Allow upload
   - If NO → Deny upload

### Order Management

Order management pages and AJAX handlers check for specific capabilities:

- Viewing order list: Requires `view_all_orders`
- Setting prices: Requires `set_order_prices`
- Updating status: Requires `manage_orders`
- Creating projects from orders: Requires `manage_projects`

## Use Cases

### Admin Can Access All Projects
**Problem**: When an admin creates a project from an order, the customer becomes the owner, but the admin cannot access it.

**Solution**: Admins automatically have `view_all_projects` and `upload_to_any_project` capabilities, so they can:
- View any project in the system
- Upload files to any project
- Manage projects created from orders

### Multiple Administrators
**Use Case**: You want to add a second administrator who can manage orders.

**Solution**: 
1. Create a new user account
2. Assign the "Administrator" role
3. They automatically get all capabilities upon plugin activation

### Project Manager Role
**Use Case**: You want a "Project Manager" who can view and manage projects but not handle orders.

**Solution**:
```php
// Add this code to your theme's functions.php or a custom plugin
add_role('project_manager', 'Project Manager', array(
    'read' => true,
    'view_all_projects' => true,
    'manage_projects' => true,
    'upload_to_any_project' => true
));
```

### Customer Support Role
**Use Case**: You want customer support staff to view orders but not modify them.

**Solution**:
```php
// Create a custom role with limited capabilities
add_role('customer_support', 'Customer Support', array(
    'read' => true,
    'view_all_orders' => true
));
```

## Security Considerations

1. **Least Privilege Principle**: Only grant capabilities that are absolutely necessary for a role
2. **Regular Audits**: Periodically review which users and roles have which capabilities
3. **Custom Roles**: Create specific roles for specific tasks rather than granting all capabilities
4. **Testing**: Always test capabilities in a staging environment before applying to production

## Troubleshooting

### Capabilities Not Working After Activation
**Solution**: Deactivate and reactivate the plugin to trigger capability assignment.

### User Still Can't Access Projects
**Check**:
1. Is the user logged in?
2. Does the user have the `view_all_projects` capability?
3. If not admin, is the user the owner or assigned to the project?

### Admin Menu Not Showing
**Check**: Ensure the user has `view_all_orders` capability.

## Migration Notes

For existing installations:
- Existing project owners maintain their access via the `user_id` field
- Existing assigned users maintain their access via the assignments table
- New capability layer adds administrator access on top of existing permissions
- No database changes needed - this is purely capability-based access control
