# Concretecms-Community-Store-SquareUp 2022
SquareUp payment gateway addon for the Concretecms Community Store

https://developer.squareup.com/reference/square

SquareUp is a very popular payment gateway. There is no fixed costs and if you already have the square hardware to take payment in person, this add-on will allow you to take payments online using the same service provider, SquareUp. 

Square uses proprietry tokenization of card information.Card data is not stored, or even seen by the backend. The front-end converts the card information to a token.The backend then only sees this token and processes the payment with the token.

##Install Community Store First.

Download a 'release' zip of the add-on, unzip this to the packages folder of your Concrete CMS installation. 
<b>BE SURE THE FOLDER NAME IS: community_store_squareup  NOTHING ELSE WILL WORK.</b>

Then install the addon via the dashboard.

Once installed, configure the payment method through the Settings/Payments dashboard section for 'Store'. You will need to log into the Square Developers website. In the Square Developers site you will be issued SANDBOX and PRODUCTION credentials. Enter these credentials in the setup fields of the add-on. 
Before going "live" please be sure to make use the Community Store to make a test purchase via the "sandbox". Check the Square Developers site to see that the test payment went through (if youv'e properly set the sandbox as teh mode, you will only see a fake charge. No funds will be moved.

Currently only Credit Card payments are active on this addon. I'm working on adding ApplePay and GooglePay.
