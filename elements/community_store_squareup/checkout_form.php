<?php defined('C5_EXECUTE') or die(_("Access Denied."));
  extract($vars);
use Square\Environment;
//set the Square sdk
$web_payment_sdk_url = "https://sandbox.web.squarecdn.com/v1/square.js";
?>
  <!-- Copyright GPL-3.0 MIT License copyright Roger Andrews 2022 -->
  
  <!-- link to the Square web payment SDK library -->
  <script type = "text/javascript" src ="<?php echo $web_payment_sdk_url ?>"> </script> 
 
 <script type = "text/javascript">
 //set Square values
 	window.applicationId = "<?php echo $ApplicationId;?>";
	window.locationId = "<?php echo $location_Id;?>";
	window.currency = "<?php echo $currency;?>";
	window.pmID = "<?php echo $pmID;?>";
 
 // insert a div to display Square information into
 	let cardDiv = document.createElement("div");
    cardDiv.id="Cardnotice";
    cardDiv.innerHTML = "";
    let cardEl = document.getElementById('store-checkout-payment-method-options');
    let parentDiv = cardEl.parentNode;
    parentDiv.insertBefore(cardDiv, cardEl);

 function toggleModal(pay) {
 //Toggle popup modal 
    if (!pay) {document.getElementById("payment-method1").checked = "checked";}
 	$(".store-payment-method-container[data-payment-method-id='" + pmID + "']").addClass('hidden');
    document.getElementsByClassName('store-payment-method-container')[0].
    classList.remove('hidden');
 } 
 
 function notice(content) {
 // display the Square Card result/error notice
   document.getElementById("Cardnotice").innerHTML = content;
}
 <!--Configure the Web Payments SDK and Card payment method-- >

 async function cardPay() {
     const payments = Square.payments(window.applicationId, window.locationId);
     const card = await payments.card();
     await card.attach('#card-container');

     async function eventHandler(event) {
         event.preventDefault();

         try {
             const result = await card.tokenize();
             if (result.status === 'OK') {
                 console.log(`Payment token is ${result.token}`);
                 let tokenField = document.getElementById('pay-token');
                 tokenField.value = result.token;
                 let pay = "true";  
                 toggleModal(pay);
                 notice('<H2>A valid Credit Card has been entered </br></H2><p class="alert alert-warning small"> Click <q>Complete Order</q> to charge your card and finish the order</p>');
               }
         } catch (e) {
             if (e.message) {
                 // window.showError(`Error: ${e.message}`);
                let  mess= "<H2>An error has occured!</br></H2><p class='alert alert-warning small'>"+e.message+"</p>";
                 notice(mess);
             } else {
                  //window.showError('Something went wrong');
             	let mess = '<H2>An error has occured!</br></H2><p class="alert alert-warning small"> Something bad has happened!</p>';
                notice(mess); 
             }
         }
     }
	const payButton = document.getElementById('pay-button');
	payButton.addEventListener('click', eventHandler);
 }
 
 cardPay();

//submit the form
</script>

<div class=store-whiteout" id="modal">
	<div class="store-cart-modal clearfix store-cart-modal-active" id="square-card">
		<a href="#" class="store-modal-exit">×</a>
    		<h3>Square Payment Gateway</h3>
      		<p class="alert alert-success"><strong>Digital Payments</strong> Enter card information</p>
      	
      	<div style="display:none;" id="errors" class="store-payment-errors square-payment-errors">
	</div> 	
					
  	<div id="card-container">
  	<!-- Card information is drawn in here by the Square API-->
	</div>
  	<form method="POST" action="squarepayment/recievetoken" id="payment-form">
  	<button class = "btn btn-success pull-right float-end" id="pay-button"  type="button">Confirm card</button>
  	<span onclick= "toggleModal()"
  		 class="pull-left float-start store-btn-cart-modal-continue btn btn-default btn-secondary" href="#"> Cancel card </span>	
	<input type="hidden" id="pay-token" name="token">
	</form>			
		
	</div>

</div>
 









