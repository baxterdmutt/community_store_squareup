# Concretecms-Community-Store-SquareUp
SquareUp payment gateway addon for the Concretecms Community Store
Stripe Checkout payment add-on for Community Store for Concrete CMS

https://developer.squareup.com/reference/square

SquareUp is a very popular payment gateway. There is no fixed costs and if you already have the square hardware to take payment in person, this add-on will allow you to take payments online using the same service provider, SquareUp. 

Square uses proprietry tokenization of card information. Card data is not stored, or even seen by the backend.The front-end converts the card information to a token. The backend then only sees this toekn and prcesses the payment with the token.

Install Community Store First.

Download a 'release' zip of the add-on, unzip this to the packages folder of your Concrete CMS installation. Then install the addon via the dashboard.

Once installed, configure the payment method through the Settings/Payments dashboard section for 'Store'. You will need to log into the Square Developers website. In the Square Developers site you will be issues SANDBOX and PRODUCTION credentials. Enter these credentials in the setup fields of the add-on. 
Before going "live" please be sure to make use the Community Store to make a test purchase via the sandbox. Check the Square Developers site to see that the test payment went through.

Currently only Credit Card payments are active on this addon. I'm working on adding ApplePay and GooglePay
