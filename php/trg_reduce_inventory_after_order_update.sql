DELIMITER $$

CREATE TRIGGER trg_reduce_inventory_after_order_update
AFTER UPDATE ON `Order`
FOR EACH ROW
BEGIN
    IF OLD.o_status = 'pending' AND NEW.o_status = 'in_transit' THEN
        UPDATE Inventory i
        JOIN Order_Detail od ON i.product_id = od.product_id
        SET i.stock_quantity = i.stock_quantity - od.quantity
        WHERE od.order_id = NEW.order_id
          AND i.pharmacy_id = NEW.pharmacy_id;
    END IF;
END$$

DELIMITER ;
