CREATE DEFINER=`root`@`localhost` FUNCTION `fn_name_subcategory_order`(_orderId INT) RETURNS text CHARSET utf8mb3 COLLATE utf8mb3_spanish_ci
    NO SQL
BEGIN
  DECLARE respuesta TEXT;
  SET respuesta = (SELECT sub_categorie_name FROM (SELECT sub_categorie_name,SUM(detail_total) AS detail_quotation FROM 
        quotations
        INNER JOIN quotations_details ON quotations_details.quotation_id = quotations.id
        INNER JOIN products ON products.id = quotations_details.product_id 
        INNER JOIN sub_categories ON sub_categories.id = products.sub_categorie 
        WHERE quotations.order_id = _orderId GROUP BY sub_categories.id) AS table_temp ORDER BY detail_quotation DESC LIMIT 1);
RETURN respuesta;
END;
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_name_subcategory_quotation`(_quotationId INT) RETURNS text CHARSET utf8mb3 COLLATE utf8mb3_spanish_ci
    NO SQL
BEGIN
  DECLARE respuesta TEXT;
  SET respuesta = (
        SELECT sub_categorie_name FROM (SELECT sub_categorie_name,SUM(detail_total) AS detail_quotation FROM quotations_details 
        INNER JOIN products ON products.id = product_id 
        INNER JOIN sub_categories ON sub_categories.id = products.sub_categorie 
        WHERE quotation_id = _quotationId GROUP BY sub_categories.id
        ) AS table_temp ORDER BY detail_quotation DESC LIMIT 1 );
RETURN respuesta;
END;