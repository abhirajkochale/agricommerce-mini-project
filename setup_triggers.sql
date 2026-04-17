DELIMITER //

DROP TRIGGER IF EXISTS restore_stock_after_cancel //

CREATE TRIGGER restore_stock_after_cancel
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
    -- Check if the status is being changed TO 'Cancelled' from any other state
    IF NEW.status = 'Cancelled' AND OLD.status != 'Cancelled' THEN
        UPDATE crops 
        SET quantity = quantity + NEW.quantity 
        WHERE id = NEW.product_id;
    END IF;
    
    -- Optional: If status is changed BACK to 'Pending' from 'Cancelled' (un-cancelling)
    -- This ensures stock is reserved again.
    IF NEW.status != 'Cancelled' AND OLD.status = 'Cancelled' THEN
        UPDATE crops 
        SET quantity = quantity - NEW.quantity 
        WHERE id = NEW.product_id;
    END IF;
END //

DELIMITER ;
