<?php

namespace CodeFarma\SkuVault;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class Client
{
	/**
	 * @var string
	 */
	protected $tenant_token;
	
	/**
	 * @var string
	 */
	protected $user_token;
	
	/**
	 * @var	GuzzleClient
	 */
	protected $http_client;
	
	/**
	 * @var	string
	 */ 
	protected $base_url;
	
	/**
	 * Set the http client
	 *
	 * @param	GuzzleClient		$client			The http client
	 * @return	void
	 */
	public function setHttpClient( GuzzleClient $client )
	{
		$this->http_client = $client;
	}
	
	/**
	 * Get the http client
	 *
	 * @return	object
	 */
	public function getHttpClient()
	{
		if ( ! isset( $this->http_client ) ) {
			$this->http_client = new GuzzleClient([
				'base_uri' => $this->base_url,
				'headers' => [
					'User-Agent' => 'SkuVault PHP Library (codefarma/php-skuvault)',
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
				],
			]);
		}
		
		return $this->http_client;
	}
	
	/**
	 * Constructor
	 *
	 * @param	string		$tenantToken				The api tenant token
	 * @param	string		$userToken					The api user token
	 * @param	bool		$use_staging				Use the staging api instead of production
	 * @return	void
	 */
	public function __construct( $tenantToken=NULL, $userToken=NULL, $use_staging=FALSE )
	{
		$this->tenant_token = $tenantToken;
		$this->user_token = $userToken;
		$this->base_url = 'https://app.skuvault.com/api/';
		
		if ( $use_staging ) {
			$this->base_url = 'https://staging.skuvault.com/api/';
		}
	}
	
	/**
	 * Get api tokens from login credentials
	 *
	 * @param	string			$email				SkuVault login email
	 * @param	string			$password			SkuVault login password
	 * @return	array
	 */
	public function getTokens( $email, $password )
	{
		return $this->makeRequest( 'POST', 'gettokens', [ 'Email' => $email, 'Password' => $password ] );
	}
	
	/**
	 * Set api tokens
	 *
	 * @param	string			$tenant_token			Skuvault api tenant token
	 * @param	string			$user_token				Skuvault api user token
	 * @return	array
	 */
	public function setTokens( $tenant_token, $user_token )
	{
		$this->tenant_token = $tenant_token;
		$this->user_token = $user_token;
	}
	
	/**
	 * Get the utc date for submission as api param
	 *
	 * @param	mixed			$date			A string, timestamp, or datetime object
	 * @return	DateTime
	 */
	public static function utcDate( $date )
	{
		if ( is_numeric( $date ) ) {
			$ts = intval( $date );
			$date = new \DateTime();
			$date->setTimestamp( $ts );
		}
		else if ( is_string( $date ) ) {
			$ts = strtotime( $date );
			$date = new \DateTime();
			$date->setTimestamp( $ts );
		}
		else if ( ! $date instanceof \DateTime ) {
			$date = new \DateTime();
		}
		
		$date->setTimezone( new \DateTimeZone("UTC") );
		
		return $date;
	}
	
	public static function apiTimeFormat( $date )
	{
		return $date->format( 'Y-m-d\TH:i:s\Z' );
	}
	
	/**
	 * Make an api request
	 *
	 * @param	string			$method				The request method
	 * @param	string			$endpoint			The api endpoint to query
	 * @param	array			$payload			The endpoint specific request data
	 * @param	array			$params				Additional params to configure the request
	 * @return	Response
	 * @throws	RequestException
	 */
	protected function makeRequest( $method, $endpoint, $payload=[], $params=[ 'timeout' => 20 ] )
	{
		/* Compose json payload with api auth */
		$params['json'] = array_merge( array( 
			'TenantToken' => $this->tenant_token,
			'UserToken' => $this->user_token,
		), $payload );
		
		$request = new Request( $method, $endpoint );
		$response = $this->getHttpClient()->send( $request, $params );
		
		return $response;
	}
	
	/**
	 * Add quantity to a warehouse location
	 *
	 * @param	array			$item			Item details
	 * @return	Response
	 * @throws  RequestException
	 */
	public function addItem( $item )
	{
		return $this->makeRequest( 'POST', 'inventory/addItem', $item );
	}
	
	/**
	 * Add quantities to warehouse locations in bulk (100 max)
	 *
	 * @param	array			$items			An array of items to add in bulk
	 * @return	Response
	 * @throws  RequestException
	 */
	public function addItemBulk( $items )
	{
		return $this->makeRequest( 'POST', 'inventory/addItemBulk', [ 'Items' => $items ] );
	}
	
	/**
	 * Add shipments to a sale
	 *
	 * @param	array			$shipments			An array of shipments to add
	 * @return	Response
	 * @throws  RequestException
	 */
	public function addShipments( $shipments )
	{
		return $this->makeRequest( 'POST', 'sales/addShipments', [ 'Shipments' => $shipments ] );
	}
	
	/**
	 * Create product brands
	 *
	 * @param	array			$shipments			An array of brands to create
	 * @return	Response
	 * @throws  RequestException
	 */
	public function createBrands( $brands )
	{
		return $this->makeRequest( 'POST', 'products/createBrands', [ 'Brands' => $brands ] );
	}
	
	/**
	 * Create sales holds
	 *
	 * @param	array			$holds				An array of holds to create
	 * @return	Response
	 * @throws  RequestException
	 */
	public function createHolds( $holds )
	{
		return $this->makeRequest( 'POST', 'sales/createHolds', [ 'Holds' => $holds ] );
	}
	
	/**
	 * Create a kit inside of SkuVault.
	 *
	 * @param	array			$kit			The product kit
	 * @return	Response
	 * @throws  RequestException
	 */
	public function createKit( $kit )
	{
		return $this->makeRequest( 'POST', 'products/createKit', $kit );
	}
	
	/**
	 * Create a PO
	 *
	 * @param	array			$purchase_order			The purchase order
	 * @return	Response
	 * @throws  RequestException
	 */
	public function createPO( $purchase_order )
	{
		return $this->makeRequest( 'POST', 'purchaseorders/createPO', $purchase_order );
	}
	
	/**
	 * Create a product
	 *
	 * @param	array			$product			The product
	 * @return	Response
	 * @throws  RequestException
	 */
	public function createProduct( $product )
	{
		return $this->makeRequest( 'POST', 'products/createProduct', $product );
	}
	
	/**
	 * Create products in bulk (100 max)
	 *
	 * @param	array			$products				An array of products to create
	 * @return	Response
	 * @throws  RequestException
	 */
	public function createProducts( $products )
	{
		return $this->makeRequest( 'POST', 'products/createProducts', [ 'Items' => $products ] );
	}
	
	/**
	 * Create suppliers
	 *
	 * @param	array			$suppliers				An array of suppliers to create
	 * @return	Response
	 * @throws  RequestException
	 */
	public function createSuppliers( $suppliers )
	{
		return $this->makeRequest( 'POST', 'products/createSuppliers', [ 'Suppliers' => $suppliers ] );
	}
	
	/**
	 * Get available quantities
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getAvailableQuantities( $params=[] )
	{
		return $this->makeRequest( 'POST', 'inventory/getAvailableQuantities', $params );
	}
	
	/**
	 * Get brands
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getBrands( $params=[] )
	{
		return $this->makeRequest( 'POST', 'products/getBrands', $params );
	}
	
	/**
	 * Get classifications
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getClassifications( $params=[] )
	{
		return $this->makeRequest( 'POST', 'products/getClassifications', $params );
	}
	
	/**
	 * Get external warehouse quantities
	 *
	 * @param	string			$warehouse_id			The warehouse id
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getExternalWarehouseQuantities( $warehouse_id, $params=[] )
	{
		$params['WarehouseId'] = $warehouse_id;
		
		return $this->makeRequest( 'POST', 'inventory/getExternalWarehouseQuantities', $params );
	}
	
	/**
	 * Get external warehouses
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getExternalWarehouses( $params=[] )
	{
		return $this->makeRequest( 'POST', 'inventory/getExternalWarehouses', $params );
	}
	
	/**
	 * Get handling time
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getHandlingTime( $params=[] )
	{
		return $this->makeRequest( 'POST', 'products/getHandlingTime', $params );
	}

	/**
	 * Get incoming items for incomplete purchase orders
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getIncomingItems( $params=[] )
	{
		return $this->makeRequest( 'POST', 'purchaseorders/getIncomingItems', $params );
	}

	/**
	 * Get integrations
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getIntegrations( $params=[] )
	{
		return $this->makeRequest( 'POST', 'integration/getIntegrations', $params );
	}

	/**
	 * Get product inventory by location
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getInventoryByLocation( $params=[] )
	{
		return $this->makeRequest( 'POST', 'inventory/getInventoryByLocation', $params );
	}

	/**
	 * Get product quantities
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getItemQuantities( $params=[] )
	{
		return $this->makeRequest( 'POST', 'inventory/getItemQuantities', $params );
	}
	
	/**
	 * Get product kit quantities
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getKitQuantities( $params=[] )
	{
		return $this->makeRequest( 'POST', 'inventory/getKitQuantities', $params );
	}

	/**
	 * Get product kits
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getKits( $params=[] )
	{
		return $this->makeRequest( 'POST', 'products/getKits', $params );
	}

	/**
	 * Get all locations in enabled warehouses
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getLocations( $params=[] )
	{
		return $this->makeRequest( 'POST', 'inventory/getLocations', $params );
	}

	/**
	 * Get a list of sales and their statuses
	 *
	 * @param	array			$order_ids				A list of order ids to get status on (max 10,000)
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getOnlineSaleStatus( $order_ids, $params=[] )
	{
		$params['OrderIds'] = array_map( 'strval', $order_ids );
		
		return $this->makeRequest( 'POST', 'sales/getOnlineSaleStatus', $params );
	}

	/**
	 * Get purchase orders
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getPOs( $params=[] )
	{
		return $this->makeRequest( 'POST', 'purchaseorders/getPOs', $params );
	}

	/**
	 * Get products
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getProducts( $params=[] )
	{
		return $this->makeRequest( 'POST', 'products/getProducts', $params );
	}

	/**
	 * Get a list of purchase order receives and receipts
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getReceivesHistory( $params=[] )
	{
		return $this->makeRequest( 'POST', 'purchaseorders/getReceivesHistory', $params );
	}

	/**
	 * Get a list of sales
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getSales( $params=[] )
	{
		return $this->makeRequest( 'POST', 'sales/getSales', $params );
	}

	/**
	 * Get a list of sales within a date range (not more than 7 days)
	 *
	 * @param	string			$from_date				The from date
	 * @param	string			$to_date				The to date
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getSalesByDate( $from_date, $to_date, $params=[] )
	{
		$params['FromDate'] = static::apiTimeFormat( static::utcDate( $from_date ) );
		$params['ToDate'] = static::apiTimeFormat( static::utcDate( $to_date ) );
		
		if ( ! isset( $params['headers'] ) ) {
			$params['headers'] = array(
				'X-API-Version' => 2,
			);
		}
		
		return $this->makeRequest( 'POST', 'sales/getSalesByDate', $params );
	}

	/**
	 * Get current shipment information
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getShipments( $params=[] )
	{
		return $this->makeRequest( 'POST', 'sales/getShipments', $params );
	}

	/**
	 * Get a list of items sold filtered by date range (max 14 days)
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getSoldItems( $start_date, $end_date, $params=[] )
	{
		$params['StartDateUtc'] = static::apiTimeFormat( static::utcDate( $start_date ) );
		$params['EndDateUtc'] = static::apiTimeFormat( static::utcDate( $end_date ) );
		
		return $this->makeRequest( 'POST', 'sales/getSoldItems', $params );
	}

	/**
	 * Get a list of current suppliers
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getSuppliers( $params=[] )
	{
		return $this->makeRequest( 'POST', 'products/getSuppliers', $params );
	}

	/**
	 * Get transaction history by date range (max 7 days)
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getTransactions( $from_date=NULL, $to_date=NULL, $params=[] )
	{
		if ( ! isset( $from_date ) ) {
			if ( ! isset( $to_date ) ) {
				$to_date = new \DateTime();
			}
			
			$from_date = static::utcDate( $to_date )->sub( new \DateInterval('P7D') );
		}
		
		if ( ! isset( $to_date ) ) {
			$to_date = static::utcDate( $from_date )->add( new \DateInterval('P7D') );
		}
		
		$params['FromDate'] = static::apiTimeFormat( static::utcDate( $from_date ) );
		$params['ToDate'] = static::apiTimeFormat( static::utcDate( $to_date ) );
		
		return $this->makeRequest( 'POST', 'inventory/getTransactions', $params );
	}

	/**
	 * Get skus and quantities from a specified warehouse
	 *
	 * @param	int				$warehouse_id			The warehouse id
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getWarehouseItemQuantities( $warehouse_id, $params=[] )
	{
		$params['WarehouseId'] = $warehouse_id;
		
		return $this->makeRequest( 'POST', 'inventory/getWarehouseItemQuantities', $params );
	}

	/**
	 * Get integrations
	 *
	 * @param	string			$sku					The product sku
	 * @param	int				$warehouse_id			The warehouse id
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getWarehouseItemQuantity( $sku, $warehouse_id, $params=[] )
	{
		$params['Sku'] = $sku;
		$params['WarehouseId'] = $warehouse_id;
		
		return $this->makeRequest( 'POST', 'inventory/getWarehouseItemQuantity', $params );
	}

	/**
	 * Get warehouses
	 *
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function getWarehouses( $params=[] )
	{
		return $this->makeRequest( 'POST', 'inventory/getWarehouses', $params );
	}

	/**
	 * Perform a pick transaction
	 *
	 * @param	string			$type					How to identify item ('sku','code')
	 * @param	string			$sku_code				Sku or code depending on $type
	 * @param	int				$warehouse_id			The warehouse id
	 * @param	int				$quantity				The quantity to pick
	 * @param	string|NULL		$location				The location to pick from, or NULL for express pick
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function pickItem( $type, $sku_code, $warehouse_id, $quantity, $location=NULL, $params=[] )
	{
		$params[ ucwords($type) ] = $sku_code;
		$params['WarehouseId'] = $warehouse_id;
		$params['Quantity'] = $quantity;
		
		if ( isset( $location ) ) {
			$params['LocationCode'] = $location;
		} else {
			$params['IsExpressPick'] = true;
		}
		
		return $this->makeRequest( 'POST', 'inventory/pickItem', $params );
	}

	/**
	 * Recieve PO Items
	 *
	 * @param	string			$po_number				The purchase order number
	 * @param	string			$supplier_name			The supplier name
	 * @param	array			$line_items				The line items to receive
	 * @param	mixed			$receipt_date			The date of the receipt, or NULL for current time
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function receivePOItems( $po_number, $supplier_name, $line_items, $receipt_date=NULL, $params=[] )
	{
		$params['PoNumber'] = $po_number;
		$params['SupplierName'] = $supplier_name;
		$params['ReceiptDate'] = static::apiTimeFormat( static::utcDate( $receipt_date ) );
		$params['LineItems'] = $line_items;
		
		return $this->makeRequest( 'POST', 'purchaseorders/receivePOItems', $params );
	}

	/**
	 * Release held quantities
	 *
	 * @param	array			$skus					An associative array of skus where the key is the sku and the value is the quantity to release
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function releaseHeldQuantities( $skus, $params=[] )
	{
		$params['SkusToRelease'] = $skus;
		
		return $this->makeRequest( 'POST', 'sales/releaseHeldQuantities', $params );
	}

	/**
	 * Remove item quantity from a warehouse location
	 *
	 * @param	string			$type					How to identify item ('sku','code')
	 * @param	string			$sku_code				Sku or code depending on $type
	 * @param	int				$warehouse_id			The warehouse id
	 * @param	int				$quantity				The quantity to pick
	 * @param	string			$location				The location to remove from
	 * @param	string			$reason					The reason (must exist in SkuVault)
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function removeItem( $type, $sku_code, $warehouse_id, $quantity, $location, $reason, $params=[] )
	{
		$params[ ucwords($type) ] = $sku_code;
		$params['WarehouseId'] = $warehouse_id;
		$params['Quantity'] = $quantity;
		$params['LocationCode'] = $location;
		$params['Reason'] = $reason;
		
		return $this->makeRequest( 'POST', 'inventory/removeItem', $params );
	}

	/**
	 * Remove item quantity from a warehouse location in bulk
	 *
	 * @param	array			$items					An array of items to remove
	 * @return	Response
	 * @throws  RequestException
	 */
	public function removeItemBulk( $items )
	{
		return $this->makeRequest( 'POST', 'inventory/removeItemBulk', [ 'Items' => $items ] );
	}

	/**
	 * Set an item quantity in a warehouse location
	 *
	 * @param	string			$type					How to identify item ('sku','code')
	 * @param	string			$sku_code				Sku or code depending on $type
	 * @param	int				$warehouse_id			The warehouse id
	 * @param	int				$quantity				The quantity to pick
	 * @param	string			$location				The location to remove from
	 * @return	Response
	 * @throws  RequestException
	 */
	public function setItemQuantity( $type, $sku_code, $warehouse_id, $quantity, $location )
	{
		$params[ ucwords($type) ] = $sku_code;
		$params['WarehouseId'] = $warehouse_id;
		$params['Quantity'] = $quantity;
		$params['LocationCode'] = $location;
		
		return $this->makeRequest( 'POST', 'inventory/setItemQuantity', $params );
	}
	
	/**
	 * Set item quantity for multiple products
	 *
	 * @param	array			$items					An array of items to update
	 * @return	Response
	 * @throws  RequestException
	 */
	public function setItemQuantities( $items )
	{
		return $this->makeRequest( 'POST', 'inventory/setItemQuantities', [ 'Items' => $items ] );
	}

	/**
	 * Attach a base64 PDF file to shipment
	 *
	 * @param	array			$shipments					An array of shipments to record
	 * @return	Response
	 * @throws  RequestException
	 */
	public function setShipmentFile( $shipments )
	{
		return $this->makeRequest( 'POST', 'sales/setShipmentFile', [ 'Shipments' => $shipments ] );
	}

	/**
	 * Sync an online sale to SkuVault.
	 *
	 * If the sale does not exist, it's created. If it does exist, it's updated. ShippingStatus is required to create sale, but not update. ItemSkus is always required. 
	 *
	 * @param	string			$order_id				The order id
	 * @param	array			$item_skus				An array of item skus with quantities to update on sale [{'Sku':'abc','Quantity':1,'UnitPrice':0.00}]
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function syncOnlineSale( $order_id, $item_skus, $params=[] )
	{
		$params['OrderId'] = $order_id;
		$params['ItemSkus'] = $item_skus;
		
		if ( isset( $params['OrderDateUtc'] ) ) {
			$params['OrderDateUtc'] = static::apiTimeFormat( static::utcDate( $params['OrderDateUtc'] ) );
		}
		
		return $this->makeRequest( 'POST', 'sales/syncOnlineSale', $params );
	}

	/**
	 * Sync multiple sales at once (max 100)
	 *
	 * @param	array			$sales					An array of sales to update
	 * @return	Response
	 * @throws  RequestException
	 */
	public function syncOnlineSales( $sales )
	{
		return $this->makeRequest( 'POST', 'sales/syncOnlineSales', [ 'Sales' => $sales ] );
	}
	
	/**
	 * Sync a shipped sale
	 *
	 * This method syncs a shipped sale and auto-removes quantity. If warehouseId isn't provided then quantity 
	 * will only be removed if the item can only be found in one location across all warehouses. If a warehouseId 
	 * is provided then quantity will be removed if the item is only found in one location within that specified 
	 * warehouse.
	 *
	 * @param	string			$order_id				The order id
	 * @param	array			$item_skus				An array of item skus with quantities to update on sale [{'Sku':'abc','Quantity':1,'UnitPrice':0.00}]
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function syncShippedSaleAndRemoveItems( $order_id, $item_skus, $params=[] )
	{
		$params['OrderId'] = $order_id;
		$params['ItemSkus'] = $item_skus;
		
		if ( isset( $params['OrderDateUtc'] ) ) {
			$params['OrderDateUtc'] = static::apiTimeFormat( static::utcDate( $params['OrderDateUtc'] ) );
		}
		
		return $this->makeRequest( 'POST', 'sales/syncShippedSaleAndRemoveItems', $params );
	}

	/**
	 * Update alternate skus and codes
	 *
	 * @param   array           $action                 Value can be 'Add', 'Delete', or 'Update'. 'Add' and 'Delete' will only modify a products' Alternates 
	 *                                                  with those specified in the call. 'Update' will overwrite a products' Alternates with the Codes/SKUs provided.
	 * @param   array           $items                  An array of items to update [{'Sku':'abc','AltSKUs':['abcde'],'AltCodes':['abc123']}]
	 * @return	Response
	 * @throws  RequestException
	 */
	public function updateAltSKUsCodes( $action, $items )
	{
		return $this->makeRequest( 'POST', 'products/updateAltSKUsCodes', [ 'Action' => $action, 'Items' => $items ] );
	}
	
	/**
	 * Update external warehouse quantities
	 *
	 * Any SKU in an external warehouse that is omitted from the next request will be removed from said warehouse.
	 * 
	 * @param	string          $warehouse_id                The external warehouse ID
	 * @param   array           $quantities                  An array of quantities to update
	 * @return	Response
	 * @throws  RequestException
	 */
	public function updateExternalWarehouseQuantities( $warehouse_id, $quantities )
	{
		return $this->makeRequest( 'POST', 'inventory/updateExternalWarehouseQuantities', [ 'WarehouseId' => $warehouse_id, 'Quantities' => $quantities ] );
	}

	/**
	 * Update handling time
	 *
	 * @param   array           $items                  An array of items to update
	 * @return	Response
	 * @throws  RequestException
	 */
	public function updateHandlingTime( $items )
	{
		return $this->makeRequest( 'POST', 'products/updateHandlingTime', [ 'Items' => $items ] );
	}

	/**
	 * Update the status of a sale
	 *
	 * @param	string			$sale_id				The sale id
	 * @param	string			$status					The sale status ('Pending','ReadyToShip','Completed','Cancelled','Invalid','ShippedUnpaid')
	 * @param	string			$type					The sale id type ('SaleId','OrderId')
	 * @return	Response
	 * @throws  RequestException
	 */
	public function updateOnlineSaleStatus( $sale_id, $status, $type='SaleId' )
	{
		return $this->makeRequest( 'POST', 'sales/updateOnlineSaleStatus', [ $type => $sale_id, 'Status' => $status ] );
	}

	/**
	 * Update existing purchase orders
	 *
	 * @param   array           $purchase_orders                  An array of purchase orders to update
	 * @return	Response
	 * @throws  RequestException
	 */
	public function updatePOs( $purchase_orders )
	{
		return $this->makeRequest( 'POST', 'purchaseorders/updatePOs', [ 'POs' => $purchase_orders ] );
	}

	/**
	 * Update a product
	 *
	 * @param	array			$sku					The sku of the product to update
	 * @param	array			$params					Additional API request parameters
	 * @return	Response
	 * @throws  RequestException
	 */
	public function updateProduct( $sku, $params=[] )
	{
		$params['Sku'] = $sku;
		
		return $this->makeRequest( 'POST', 'products/updateProduct', $params );
	}

	/**
	 * Update multiple products (max 100)
	 *
	 * @param	array			$items					An array of items to update
	 * @return	Response
	 * @throws  RequestException
	 */
	public function updateProducts( $items )
	{
		return $this->makeRequest( 'POST', 'products/updateProducts', [ 'Items' => $items ] );
	}

	/**
	 * Update multiple shipments
	 *
	 * @param	array			$shipments					An array of shipments to update
	 * @return	Response
	 * @throws  RequestException
	 */
	public function updateShipments( $shipments )
	{
		return $this->makeRequest( 'POST', 'sales/updateShipments', [ 'Shipments' => $shipments ] );
	}

	/**
	 * Get Lot Location Quantity
	 * 
	 * @param	string			$file				The file path to be read
	 * @param	$array  		$query				The query to execute
	 * @return	array
	 * @throws  Exception
	 */
	public function getLotQuantitiesByLocation( $file, $query )
	{
		$items = $fields = array(); $i = 0;
		if (($handle = fopen($file, 'r')) !== FALSE) { // Check the resource is valid
			while (($row = fgetcsv($handle, 4096)) !== false) {
				if (empty($fields)) {
					foreach( $row as $k ){
						$k = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u','', $k);
						$k = preg_replace('/\s*/', '', ucwords($k));
						array_push($fields, $k);
					}
					continue;
				}
				foreach ($row as $k=>$value) {
					$items[$i][$fields[$k]] = $value;
				}
				$i++;
			}
			if (!feof($handle)) {
				throw new Exception('Error: unexpected fgets() fail.');
			}
			fclose($handle);

			return array_slice($items, $query['PageSize'] * $query['PageNumber'], $query['PageSize'] );
		}
	}
	
}