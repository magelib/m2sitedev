<?php
namespace Dotsquares\DeleteOrders\Controller\Adminhtml\Order;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResourceConnection;
class MultipleDelete extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
  public $_resource;

  public function __construct(Context $context,
  ResourceConnection $resource,
  Filter $filter, CollectionFactory $collFactory)
    {
        
    $this->_resource = $resource;
    parent::__construct($context , $filter);
    $this->collectionFactory = $collFactory;
 
  }
    protected function massAction(AbstractCollection $dataCollection)
    {
		$countCancelOrder = 0;
        $dbConnection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $showTables = $dbConnection->fetchCol('show tables');
        
        $tblSalesOrder = $dbConnection->getTableName('sales_order');
		$tblSalesOrderAddress = $dbConnection->getTableName('sales_order_address');
        $tblSalesOrderItem = $dbConnection->getTableName('sales_order_item');
        $tblSalesOrderPayment = $dbConnection->getTableName('sales_order_payment');
        $tblSalesOrderStatusHistory = $dbConnection->getTableName('sales_order_status_history');
        $tblSalesOrderGrid = $dbConnection->getTableName('sales_order_grid');
        $tblSalesInvoice = $dbConnection->getTableName('sales_invoice');
        $tblSalesInvoiceItem = $dbConnection->getTableName('sales_invoice_item');
        $tblSalesInvoiceGrid = $dbConnection->getTableName('sales_invoice_grid');
        $tblSalesInvoiceComment = $dbConnection->getTableName('sales_invoice_comment');
        $tblSalesShipment = $dbConnection->getTableName('sales_shipment');
        $tblSalesShipmentItem = $dbConnection->getTableName('sales_shipment_item');
        $tblSalesShipmentTrack = $dbConnection->getTableName('sales_shipment_track');
        $tblSalesShipmentGrid = $dbConnection->getTableName('sales_shipment_grid');
		$tblSalesShipmentComment = $dbConnection->getTableName('sales_shipment_comment');
        $tblSalesCreditmemo = $dbConnection->getTableName('sales_creditmemo');
        $tblSalesCreditmemoItem = $dbConnection->getTableName('sales_creditmemo_item');
        $tblSalesCreditmemoGrid = $dbConnection->getTableName('sales_creditmemo_grid');
		$tblSalesCreditmemoComment = $dbConnection->getTableName('sales_creditmemo_comment');
		$tblQuote = $dbConnection->getTableName('quote');
        $tblQuoteItem = $dbConnection->getTableName('quote_item');
		$tblQuoteItemOption = $dbConnection->getTableName('quote_item_option');
        $tblQuoteAddress = $dbConnection->getTableName('quote_address');
		$tblQuoteAddressItem = $dbConnection->getTableName('quote_address_item');
        $tblQuotePayment = $dbConnection->getTableName('quote_payment');
        $tblQuoteShippingRate = $dbConnection->getTableName('quote_shipping_rate');
        $tblQuoteIDMask = $dbConnection->getTableName('quote_id_mask');
        $tblLogQuote = $dbConnection->getTableName('log_quote');
        $showTablesLog = $dbConnection->fetchCol('SHOW TABLES LIKE ?', '%'.$tblLogQuote);
        $tblSalesOrderTax = $dbConnection->getTableName('sales_order_tax');       
        foreach ($dataCollection->getItems() as $order) {

                $orderId = $order->getId();
                if ($order->getIncrementId()) {
                    $incId = $order->getIncrementId();
                    if (in_array($tblSalesOrder, $showTables)) {
                        $results = $dbConnection->fetchAll('SELECT quote_id FROM `'.$tblSalesOrder.'` WHERE entity_id='.$orderId);
                        $quoteId = (int) $results[0]['quote_id'];
                    }
                    $dbConnection->rawQuery('SET FOREIGN_KEY_CHECKS=1');
					if (in_array($tblSalesOrder, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesOrder.'` WHERE entity_id='.$orderId);
                    }
                    if (in_array($tblSalesOrderAddress, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesOrderAddress.'` WHERE parent_id='.$orderId);
                    }
                    if (in_array($tblSalesOrderItem, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesOrderItem.'` WHERE order_id='.$orderId);
                    }
                    if (in_array($tblSalesOrderPayment, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesOrderPayment.'` WHERE parent_id='.$orderId);
                    }
                    if (in_array($tblSalesOrderStatusHistory, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesOrderStatusHistory.'` WHERE parent_id='.$orderId);
                    }
                    if ($incId && in_array($tblSalesOrderGrid, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesOrderGrid.'` WHERE increment_id='.$incId);
                    }
                    if (in_array($tblSalesCreditmemoComment, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesCreditmemoComment.'` WHERE parent_id IN (SELECT entity_id FROM `'.$tblSalesCreditmemo.'` WHERE order_id='.$orderId.')');
                    }
                    if (in_array('sales__creditmemo_item', $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesCreditmemoItem.'` WHERE parent_id IN (SELECT entity_id FROM `'.$tblSalesCreditmemo.'` WHERE order_id='.$orderId.')');
                    }
                    if (in_array($tblSalesCreditmemo, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesCreditmemo.'` WHERE order_id='.$orderId);
                    }
                    if (in_array($tblSalesCreditmemoGrid, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesCreditmemoGrid.'` WHERE order_id='.$orderId);
                    }
                    if (in_array($tblSalesInvoiceComment, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesInvoiceComment.'` WHERE parent_id IN (SELECT entity_id FROM `'.$tblSalesInvoice.'` WHERE order_id='.$orderId.')');
                    }
                    if (in_array($tblSalesInvoiceItem, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesInvoiceItem.'` WHERE parent_id IN (SELECT entity_id FROM `'.$tblSalesInvoice.'` WHERE order_id='.$orderId.')');
                    }
                    if (in_array($tblSalesInvoice, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesInvoice.'` WHERE order_id='.$orderId);
                    }
                    if (in_array($tblSalesInvoiceGrid, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesInvoiceGrid.'` WHERE order_id='.$orderId);
                    }
                    if (in_array($tblSalesShipmentComment, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesShipmentComment.'` WHERE parent_id IN (SELECT entity_id FROM `'.$tblSalesShipment.'` WHERE order_id='.$orderId.')');
                    }
                    if (in_array($tblSalesShipmentItem, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesShipmentItem.'` WHERE parent_id IN (SELECT entity_id FROM `'.$tblSalesShipment.'` WHERE order_id='.$orderId.')');
                    }
                    if (in_array($tblSalesShipmentTrack, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesShipmentTrack.'` WHERE order_id IN (SELECT entity_id FROM `'.$tblSalesShipment.'` WHERE parent_id='.$orderId.')');
                    }
                    if (in_array($tblSalesShipment, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesShipment.'` WHERE order_id='.$orderId);
                    }
                    if (in_array($tblSalesShipmentGrid, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesShipmentGrid.'` WHERE order_id='.$orderId);
                    }
					if ($quoteId) {
                        if (in_array($tblQuoteAddressItem, $showTables)) {
                            $dbConnection->rawQuery('DELETE FROM `'.$tblQuoteAddressItem.'` WHERE parent_item_id IN (SELECT address_id FROM `'.$tblQuoteAddress.'` WHERE quote_id='.$quoteId.')');
                        }
                        if (in_array($tblQuoteShippingRate, $showTables)) {
                            $dbConnection->rawQuery('DELETE FROM `'.$tblQuoteShippingRate.'` WHERE address_id IN (SELECT address_id FROM `'.$tblQuoteAddress.'` WHERE quote_id='.$quoteId.')');
                        }
                       if (in_array($tblQuoteIDMask, $showTables)) {
                           $dbConnection->rawQuery('DELETE FROM `'.$tblQuoteIDMask.'` where quote_id='.$quoteId);
                        }
                        if (in_array($tblQuoteItemOption, $showTables)) {
                            $dbConnection->rawQuery('DELETE FROM `'.$tblQuoteItemOption.'` WHERE item_id IN (SELECT item_id FROM `'.$tblQuoteItem.'` WHERE quote_id='.$quoteId.')');
                        }
                        if (in_array($tblQuote, $showTables)) {
                            $dbConnection->rawQuery('DELETE FROM `'.$tblQuote.'` WHERE entity_id='.$quoteId);
                        }
                        if (in_array($tblQuoteAddress, $showTables)) {
                            $dbConnection->rawQuery('DELETE FROM `'.$tblQuoteAddress.'` WHERE quote_id='.$quoteId);
                        }
                        if (in_array($tblQuoteItem, $showTables)) {
                            $dbConnection->rawQuery('DELETE FROM `'.$tblQuoteItem.'` WHERE quote_id='.$quoteId);
                        }
                        if (in_array('sales__quotePayment', $showTables)) {
                            $dbConnection->rawQuery('DELETE FROM `'.$tblQuotePayment.'` WHERE quote_id='.$quoteId);
                        }
                    }
                    if (in_array($tblSalesOrderTax, $showTables)) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblSalesOrderTax.'` WHERE order_id='.$orderId);
                    }
                    if ($quoteId && $showTablesLog) {
                        $dbConnection->rawQuery('DELETE FROM `'.$tblLogQuote.'` WHERE quote_id='.$quoteId);
                    }
                    $dbConnection->rawQuery('SET FOREIGN_KEY_CHECKS=1');
                }

            $countCancelOrder++;
        }
        $countNonCancelOrder = $dataCollection->count() - $countCancelOrder;

        if ($countNonCancelOrder && $countCancelOrder) {
            $this->messageManager->addError(__('%1 order(s) cannot be deleted.', $countNonCancelOrder));
        } elseif ($countNonCancelOrder) {
            $this->messageManager->addError(__('You cannot delete the order(s).'));
        }

        if ($countCancelOrder) {
			if($countCancelOrder == 1){
				$this->messageManager->addSuccess(__('%1 order has been deleted .', $countCancelOrder));
			}
			else{
				$this->messageManager->addSuccess(__('%1 orders have been deleted .', $countCancelOrder));
			}
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('sales/*/');
        return $redirectResult;
    }
}
