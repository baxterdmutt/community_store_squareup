<?php
namespace Concrete\Package\CommunityStoreSquareup\Src\CommunityStore\Payment\Methods\CommunityStoreSquareup;

use Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;
use Core;
use Log;
use Config;
use Exception;
use Square\SquareClient;
use Square\Models\Money;
use Square\Models\CreatePaymentRequest;
use Square\Exceptions\ApiException;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;

class CommunityStoreSquareupPaymentMethod extends StorePaymentMethod
{

    public function dashboardForm()
    {
        $this->set('squareMode', Config::get('community_store_squareup.mode'));
        $this->set('squareCurrency', Config::get('community_store_squareup.currency'));
        $this->set('squareSandboxApplicationId', Config::get('community_store_squareup.sandboxApplicationId'));
        $this->set('squareSandboxAccessToken', Config::get('community_store_squareup.sandboxAccessToken'));
        $this->set('squareSandboxLocation', Config::get('community_store_squareup.sandboxLocation'));
        $this->set('squareLiveApplicationId', Config::get('community_store_squareup.liveApplicationId'));
        $this->set('squareLiveAccessToken', Config::get('community_store_squareup.liveAccessToken'));
        $this->set('squareLiveLocation', Config::get('community_store_squareup.liveLocation'));
        $this->set('form', Core::make("helper/form"));

        $gateways = array(
            'square_form'=>'Form'
        );

        $this->set('squareGateways', $gateways);

        $currencies = array(
          'UAD'=>t('Australian Dollars'),
          'CAD'=>t('Canadian Dollar'),
          'JPY'=>t('Japanese Yen'),
          'USD'=>t('US Dollars'),
          'GBP'=>t('British Pound'),
          'EUR'=>t('Euro')
        );

        $this->set('squareCurrencies', $currencies);
    }
  
    public function save(array $data = [])
    {
        Config::save('community_store_squareup.mode', $data['squareMode']);
        Config::save('community_store_squareup.currency', $data['squareCurrency']);
        Config::save('community_store_squareup.sandboxApplicationId', $data['squareSandboxApplicationId']);
        Config::save('community_store_squareup.sandboxAccessToken', $data['squareSandboxAccessToken']);
        Config::save('community_store_squareup.sandboxLocation', $data['squareSandboxLocation']);
        Config::save('community_store_squareup.liveApplicationId', $data['squareLiveApplicationId']);
        Config::save('community_store_squareup.liveAccessToken', $data['squareLiveAccessToken']);
        Config::save('community_store_squareup.liveLocation', $data['squareLiveLocation']);
    }
  
    public function validate($args, $e)
    {
        return $e;
    }
 
    public function checkoutForm()
    {
        $customer = new StoreCustomer();
        $currency = Config::get('community_store_squareup.currency');
        $mode =  Config::get('community_store_squareup.mode');
  
  //      $mode = Config::get('community_store_squareup.mode');
        $this->set('mode', $mode);
        $this->set('currency', Config::get('community_store_squareup.currency'));

        if ($mode == 'live') {
            $this->set('ApplicationId', Config::get('community_store_squareup.liveApplicationId'));
            $this->set('location_Id', Config::get('community_store_squareup.liveLocation'));
        } else {
            $this->set('ApplicationId', Config::get('community_store_squareup.sandboxApplicationId'));
            $this->set('location_Id', Config::get('community_store_squareup.sandboxLocation'));
        }
      
        $this->set('email', $customer->getEmail());
        $this->set('form', Core::make("helper/form"));

//	All Square permitted currency except Japanese YEN are in cents so need to be multiplied by 100
//  Let the StorePrice Utility take care of the calculation 
        $currencyMultiplier = StorePrice::getCurrencyMultiplier($currency);
        $this->set('amount', number_format(StoreCalculator::getGrandTotal() * $currencyMultiplier, 0, '', ''));
        
//	Prepare for store payment method (ie: invoice or square)
        $this->set('pmEnabled', StorePaymentMethod::getEnabledMethods());
        $pmID = StorePaymentMethod::getByHandle('community_store_squareup')->getID();
        $this->set('pmID', $pmID);
        $years = array();
        $year = date("Y");
        for ($i=0; $i<15; $i++) {
            $years[$year+$i] = $year+$i;
        }
        $this->set("years", $years);
    }

    public function submitPayment()
    {
        $currency = Config::get('community_store_squareup.currency');
        $mode =  Config::get('community_store_squareup.mode');
        if ($mode == 'sandbox') {
            $Access_Token = Config::get('community_store_squareup.sandboxAccessToken');
        } else {
            $Access_Token = Config::get('community_store_squareup.liveAccessToken');
        }
// Alert for debugging purposes only
        // Log::addEntry("Start with submitPayment", t('Community Store Square'));
 
//Alert for debuggin only
//Log::addEntry("Token: " . $token , t('Community Store Square'));

//Token errors are captured in the checkout_form by square javascript.

// Set up the Square Client API
        $square_client = new SquareClient([
        'accessToken' => $Access_Token,
        'environment' => $mode
//'userAgentDetail' => 'custom_site' // Remove or replace this detail when building your own app
//not needed under normal circumstances
        ]);
        $token = $_POST['token'];
        $payments_api = $square_client->getPaymentsApi();

// To learn more about splitting payments with additional recipients,
// see the Payments API documentation on our [developer site]
// (https://developer.squareup.com/docs/payments-api/overview).

        if ($currency == 'JPY') {
            $total_charge = StoreCalculator::getGrandTotal()*1;
        } else {
            $total_charge = StoreCalculator::getGrandTotal()*100;
        }

        $money = new Money();
// Monetary amounts are specified in the smallest unit of the applicable currency.
// This amount is in cents. It's also hard-coded for $1.00, which isn't very useful.
        $money->setAmount($total_charge);

// Set currency to the currency for the location
        $money -> setCurrency($currency);

        function guidv4($data = null)
        {
//Generate RFC 4122 compliant UUID
// Generate 16 bytes (128 bits) of random data or use the data passed into the function.
            $data = $data ?? random_bytes(16);
            assert(strlen($data) == 16);

    // Set version to 0100
                    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

// Every payment you process with the SDK must have a unique idempotency key.
// If you're unsure whether a particular payment succeeded, you can reattempt
// it with the same idempotency key without worrying about double charging
// the buyer.

        $idempotencyKey = guidv4();

//Prepare payment request
        $create_payment_request = new CreatePaymentRequest($token, $idempotencyKey, $money);
        try {
//Submit the payment request and recieve response
//Alert for Debugging
//Log::addEntry("Start Square payment submission", t(Community Store Square'));
 
            $response = $payments_api->createPayment($create_payment_request);

//Test response for success or failure
//Alert for Debugging only
//Log::addEntry('Square payment result'."\n". $response."\n", t(Community Store Square'));

            if ($response->isSuccess()) {
                $LogResponse = json_encode($response->getResult());
                $payment_id = $response->getResult()->getPayment()->getId();
                $oRefId = $response->getResult()->getPayment()->getReferenceId();

//Alert for Debugging only
//Log::addEntry('Square payment result'."\n". $LogResponse."\n", t(Community Store Square'));	
                return array('error'=>0, 'transactionReference'=>$payment_id);
            } else {
//Read the errors
                $LogResponse = json_decode(json_encode($response->getErrors())); //gives array
                $category = $LogResponse[0]->category;
                $code = $LogResponse[0]->code;
                $detail = $LogResponse[0]->detail;
                $field = $LogResponse[0]->field;
//Log Error to Backend
                Log::addEntry("Square Response Error: " . $detail, t('Community Store Square'));
// Display error detail to front end
                return array('error'=>1, 'errorMessage'=>'Code: ' . $code . 'Detail: ' . $detail);
            }
        } catch (ApiException $e) {
            return array ('error'=>1, 'errorMessage '=>'An unknown error occured');
        }
    }

    public function getPaymentMethodName()
    {
        return 'Square';
    }

    public function getPaymentMethodDisplayName()
    {
        return $this->getPaymentMethodName();
    }

    public function getName()
    {
        return $this->getPaymentMethodName();
    }
}

return __NAMESPACE__;