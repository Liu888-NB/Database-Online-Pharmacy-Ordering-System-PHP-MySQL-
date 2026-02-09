DELIMITER $$

CREATE TRIGGER trg_check_inventory_before_order_update
BEFORE UPDATE ON `Order`
FOR EACH ROW
BEGIN
    DECLARE insufficient_stock INT DEFAULT 0;

    IF OLD.o_status = 'pending' AND NEW.o_status = 'in_transit' THEN
        SELECT COUNT(*) INTO insufficient_stock
        FROM Order_Detail od
        JOIN Inventory i ON od.product_id = i.product_id
        WHERE od.order_id = NEW.order_id
          AND i.pharmacy_id = NEW.pharmacy_id
          AND od.quantity > i.stock_quantity;

        IF insufficient_stock > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = '库存不足，无法提交订单';
        END IF;
    END IF;
END$$

DELIMITER ;
