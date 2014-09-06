<?php 

class SteamAPIException extends \Exception {}
class ImgurAPIException extends \Exception {}

class Listing_MissingItem extends \Exception {}
class Listing_Duplicate extends \Exception {}
class Listing_StorageFull extends \Exception {}
class Listing_InvalidDetails extends \Exception {}

class User_TooNew extends \Exception {}
class User_SteamBanned extends \Exception {}
class User_RefreshInventory extends \Exception {}
class User_InventoryError extends \Exception {}

class Bulk_NotUniform extends \Exception {}
class Bulk_NotStackable extends \Exception {}
class Bulk_InsufficientStock extends \Exception {}

class Order_ReservationError extends \Exception {}
class PayPal_CheckoutError extends \Exception {}
class PayPal_VerificationError extends \Exception {}
class Coinbase_CheckoutError extends \Exception {}
class PayPal_CashoutError extends \Exception {}
class Coinbase_CashoutError extends \Exception {}

class Hashids_Invalid extends \Exception {}

class Admin_ConcurrencyException extends \Exception {}