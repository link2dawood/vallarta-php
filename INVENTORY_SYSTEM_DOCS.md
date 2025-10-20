# 420 Vallarta Inventory Management System

## Overview
The inventory management system has been enhanced to automatically update stock levels when orders are created or edited. This ensures accurate inventory tracking and prevents overselling.

## Key Features

### Automatic Inventory Updates
- **Order Creation**: Stock is automatically reduced when orders are placed
- **Order Editing**: Stock levels are adjusted based on quantity changes
- **Inventory Logging**: All changes are logged with user ID, date, and reason

### Stock Validation
- **Order Validation**: Checks if sufficient stock exists before allowing orders
- **Low Stock Alerts**: Identifies products with low inventory levels
- **Negative Stock Prevention**: Prevents stock from going below zero

## Database Schema

### Movies Table (Products)
- `movie_id`: Product ID
- `title`: Product name
- `unit`: Current stock quantity
- `price`: Product price

### Inventory Table (Stock Changes Log)
- `user_id`: User who made the change
- `product_id`: Product affected
- `qnt_add`: Quantity change (positive = added, negative = removed)
- `date`: Timestamp of change

### Orders Table (ordere)
- `total_products`: Text field containing order details in format "Product Name (quantity), Product Name (quantity)"

## File Structure

### Core Files
- `settings/inventory_functions.php`: Core inventory management functions
- `Edit_order.php`: Enhanced order editing with inventory updates
- `checkout.php`: Order processing with automatic stock reduction
- `test_inventory.php`: Test interface for inventory functions

### Key Functions

#### parseOrderProducts($order_string)
Parses order details string and returns array of products with quantities.

**Input Format**: "Product Name (quantity), Product Name (quantity)"

**Example**: "OG Kush Premium (2), Sour Diesel (1)"

#### updateInventoryOnOrderChange($order_id, $old_order, $new_order, $user_id)
Compares old and new orders and updates inventory accordingly.

#### updateProductStock($product_id, $quantity_change, $user_id, $reason)
Updates stock for a specific product and logs the change.

#### validateOrderStock($order_string)
Validates that sufficient stock exists for an order.

#### getLowStockAlerts($threshold)
Returns products with stock below the specified threshold.

## Usage Examples

### Order Format
Orders must follow this exact format:
```
Product Name (quantity), Product Name (quantity)
```

**Valid Examples:**
- "OG Kush Premium (2)"
- "OG Kush Premium (2), Sour Diesel (1)"
- "Live Resin Cart - Wedding Cake (1), Glass Water Pipe - 12 inch (1)"

**Invalid Examples:**
- "OG Kush Premium 2" (missing parentheses)
- "OG Kush Premium (2 pieces)" (non-numeric quantity)

### Editing Orders
When editing an order:
1. Parse the old order to get previous quantities
2. Parse the new order to get updated quantities
3. Calculate the difference for each product
4. Update stock levels accordingly
5. Log all changes in the inventory table

**Example Scenario:**
- Old Order: "OG Kush Premium (2), Sour Diesel (1)"
- New Order: "OG Kush Premium (1), Test 1 (2)"

**Result:**
- OG Kush Premium: +1 to stock (reduced order)
- Sour Diesel: +1 to stock (removed from order)
- Test 1: -2 from stock (added to order)

## Testing

### Test Interface
Access `test_inventory.php` (admin login required) to:
- Test order parsing functionality
- Manually update stock levels
- Simulate order edits
- Check low stock alerts
- View current product inventory

### Manual Testing Steps
1. Log in as admin user
2. Go to `test_inventory.php`
3. Run various tests to verify functionality
4. Check inventory changes in the database
5. Verify stock levels are correct

## Security Considerations

### Input Validation
- All user inputs are sanitized using `mysqli_real_escape_string()`
- Order quantities are validated as positive integers
- Product names are validated against existing products

### Access Control
- Only logged-in users can modify inventory
- Admin privileges required for test interface
- All changes are logged with user attribution

## Troubleshooting

### Common Issues

**Stock Goes Negative**
- System prevents negative stock automatically
- Check inventory logs for unauthorized changes
- Verify order parsing is working correctly

**Order Parsing Fails**
- Ensure order format follows exact specification
- Check for special characters in product names
- Verify products exist in database

**Inventory Not Updating**
- Check database connection
- Verify user permissions
- Review error logs for SQL issues

### Error Messages
The system provides detailed error messages for:
- Insufficient stock when placing orders
- Invalid order format
- Database connection issues
- Missing products

## Integration Points

### Existing Systems
- **Cart System**: Integrates with existing cart functionality
- **Order Management**: Works with current order processing
- **User Management**: Uses existing user authentication
- **Email System**: Compatible with order confirmation emails

### Future Enhancements
- Real-time stock alerts
- Automated reorder points
- Supplier integration
- Barcode scanning support
- Mobile inventory management

## Maintenance

### Regular Tasks
- Monitor low stock alerts daily
- Review inventory logs weekly
- Backup inventory data regularly
- Update product information as needed

### Performance
- Index on product_id and user_id columns
- Regular cleanup of old inventory logs
- Monitor database size growth
- Optimize queries for large datasets

## Support

For technical support or questions about the inventory system:
1. Check the test interface for diagnostic information
2. Review database logs for error details
3. Verify all files are properly uploaded
4. Ensure database permissions are correct

## Version History

### Version 1.0 (Current)
- Automatic inventory updates on order creation/editing
- Stock validation and low stock alerts
- Comprehensive logging system
- Test interface for verification
- Integration with existing order system