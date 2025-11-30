# TourLink Architecture - Payment System & Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                     TOURLINK PAYMENT SYSTEM                          │
└─────────────────────────────────────────────────────────────────────┘

                           FRONTEND (Browser)
                    ┌──────────────────────────────┐
                    │  booking_payment.php          │
                    │  - Booking Display           │
                    │  - Discount Code Input       │
                    │  - Payment Button            │
                    └──────────────────────────────┘
                                  │
                                  │ AJAX/Redirect
                                  ▼
┌──────────────────────────────────────────────────────────────────┐
│                        BACKEND (PHP)                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │   /actions/paystack_init_transaction.php                │   │
│  │   - Validate booking & email                            │   │
│  │   - Apply discount code (if provided)                   │   │
│  │   - Generate transaction reference                     │   │
│  │   - Call Paystack initialize API                        │   │
│  │   - Store payment session data                          │   │
│  │   - Return authorization URL                            │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           │                                       │
│                           ▼                                       │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │        PAYSTACK PAYMENT GATEWAY (External)              │   │
│  │   https://checkout.paystack.com/                        │   │
│  │   - Customer enters card/mobile money details           │   │
│  │   - 3D Secure verification                              │   │
│  │   - Payment processing                                  │   │
│  │   - Redirect back with reference                        │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           │                                       │
│                           ▼                                       │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │   /view/paystack_callback.php                           │   │
│  │   - Receive reference from Paystack                     │   │
│  │   - Display loading/verification screen                 │   │
│  │   - Trigger payment verification                        │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           │                                       │
│                           ▼                                       │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │   /actions/paystack_verify_payment.php                  │   │
│  │   - Call Paystack verify API                            │   │
│  │   - Validate payment status                             │   │
│  │   - Validate amount                                     │   │
│  │   - Update booking payment_status to 'paid'             │   │
│  │   - Record payment in tl_payments                       │   │
│  │   - Record discount usage (if applied)                  │   │
│  │   - Calculate commission (15%)                          │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           │                                       │
│                           ▼                                       │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │        DATABASE (MySQL)                                 │   │
│  │   ├── tl_bookings (payment_status updated)              │   │
│  │   ├── tl_payments (payment recorded)                    │   │
│  │   ├── tl_discount_usage (discount recorded)             │   │
│  │   └── tl_discount_codes (usage_count updated)           │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
                           FRONTEND (Browser)
                    ┌──────────────────────────────┐
                    │  payment_success.php         │
                    │  - Show booking confirmation │
                    │  - Display booking reference  │
                    │  - Show payment reference     │
                    │  - Navigation options         │
                    └──────────────────────────────┘
```

## Payment Flow Sequence Diagram

```
Tourist       Frontend         Backend              Paystack         Database
   │              │               │                     │                 │
   │ View          │               │                     │                 │
   │ Booking       │               │                     │                 │
   │ Payment       │               │                     │                 │
   ├─────────────>│               │                     │                 │
   │              │               │                     │                 │
   │ Enter         │               │                     │                 │
   │ Discount      │               │                     │                 │
   │ Code          │               │                     │                 │
   ├─────────────>│               │                     │                 │
   │              │               │                     │                 │
   │ Validate      │               │                     │                 │
   │ Discount      │               │                     │                 │
   │              ├──────────────>│                     │                 │
   │              │               │                     │                 │
   │              │  Discount     │                     │                 │
   │              │  Applied      │                     │                 │
   │              │<──────────────┤                     │                 │
   │              │               │                     │                 │
   │ Click Pay    │               │                     │                 │
   │ Now          │               │                     │                 │
   ├─────────────>│               │                     │                 │
   │              │               │                     │                 │
   │ POST         │               │                     │                 │
   │ Init Payment │               │                     │                 │
   │              ├──────────────>│                     │                 │
   │              │               │                     │                 │
   │              │               │ Validate Booking   │                 │
   │              │               │ Apply Discount     │                 │
   │              │               │ Generate Ref       │                 │
   │              │               │ Initialize API Call│                 │
   │              │               ├────────────────────>│                 │
   │              │               │                     │                 │
   │              │               │  Auth URL +        │                 │
   │              │               │  Reference         │                 │
   │              │               │<────────────────────┤                 │
   │              │               │                     │                 │
   │ Redirect     │               │                     │                 │
   │ to Paystack  │<──────────────┤                     │                 │
   ├─────────────────────────────>│                     │                 │
   │              │               │                     │                 │
   │ Enter Card   │               │                     │                 │
   │ / Mobile     │               │                     │                 │
   │ Money        │               │                     │                 │
   ├─────────────────────────────────────────────────>│                 │
   │              │               │                     │                 │
   │ 3D Secure    │               │                     │                 │
   │ Verify       │               │                     │                 │
   ├─────────────────────────────────────────────────>│                 │
   │              │               │                     │                 │
   │ Complete     │               │                     │                 │
   │ Payment      │               │                     │                 │
   ├─────────────────────────────────────────────────>│                 │
   │              │               │                     │                 │
   │ Redirect     │               │                     │                 │
   │ to Callback  │<───────────────────────────────────┤                 │
   ├─────────────>│               │                     │                 │
   │              │               │                     │                 │
   │              │  POST Verify  │                     │                 │
   │              │  Payment      │                     │                 │
   │              ├──────────────>│                     │                 │
   │              │               │                     │                 │
   │              │               │ Call Verify API    │                 │
   │              │               ├────────────────────>│                 │
   │              │               │                     │                 │
   │              │               │ Transaction Data   │                 │
   │              │               │<────────────────────┤                 │
   │              │               │                     │                 │
   │              │               │ Validate Status    │                 │
   │              │               │ Validate Amount    │                 │
   │              │               │ Update Booking     ├────────────────>│
   │              │               │ Record Payment     ├────────────────>│
   │              │               │ Record Discount    ├────────────────>│
   │              │               │                     │                 │
   │              │ Success JSON  │                     │                 │
   │              │<──────────────┤                     │                 │
   │              │               │                     │                 │
   │ Redirect     │               │                     │                 │
   │ Success Page │<──────────────┤                     │                 │
   ├─────────────>│               │                     │                 │
   │              │               │                     │                 │
   │ View Booking │               │                     │                 │
   │ Confirmation │               │                     │                 │
   └──────────────┘               │                     │                 │
                                  │                     │                 │
```

## Transaction Reference Flow

```
Tourist ID: 5
Booking ID: 42
Current Timestamp: 1699876543

Reference Generation:
┌─────────────────────────────────────────┐
│ TL-42-5-1699876543                      │
├─────────────────────────────────────────┤
│ TL           = TourLink identifier       │
│ 42           = Booking ID                 │
│ 5            = Tourist ID                │
│ 1699876543   = Unix timestamp            │
└─────────────────────────────────────────┘

Storage Path:
Session Data (Temporary)
├── paystack_ref: "TL-42-5-1699876543"
├── paystack_booking_id: 42
├── paystack_amount: 150.50
├── paystack_discount_code: "SAVE10"
├── paystack_discount_id: 3
└── paystack_discount_amount: 15.05

Database Storage:
tl_payments Table
├── booking_id: 42
├── transaction_ref: "TL-42-5-1699876543"
├── payment_method: "paystack"
├── payment_channel: "card"
├── authorization_code: "xxxxx..."
├── amount: 135.45
└── payment_status: "successful"

tl_bookings Table
├── booking_id: 42
├── payment_status: "paid"
├── discount_amount: 15.05
└── total_amount: 135.45
```

## Error Handling Flow

```
                         Error Occurs?
                              │
                    ┌─────────┴─────────┐
                    │                   │
            Network Error        Validation Error
                    │                   │
                    ▼                   ▼
        Connection Attempt         Validate Data
        Fails to Paystack          (Email, Booking, etc)
                    │                   │
                    │                   ├─ Invalid Email
                    │                   │  "Invalid email address"
                    │                   │
                    │                   ├─ Booking Not Found
                    │                   │  "Booking not found"
                    │                   │
                    │                   ├─ Already Paid
                    │                   │  "This booking has already been paid"
                    │                   │
                    │                   ├─ Invalid Discount
                    │                   │  "Invalid or expired discount code"
                    │                   │
                    │                   └─ Amount Too Small
                    │                      "Payment amount too small"
                    │
                    ▼
            Paystack API Error
                    │
        ┌───────────┼────────────┐
        │           │            │
    Invalid      Payment      API Rate
    Reference    Failed       Limited
        │           │            │
        ▼           ▼            ▼
    "No such    "Payment    "API Error
     reference" status"     Retry later"

                    │
                    ▼
        Send Error to Frontend
        JSON Response
        {"status": "error", "message": "..."}
                    │
                    ▼
        Show Alert/Toast Notification
        to Tourist
                    │
                    ▼
        Allow Retry
        └─> Back to Booking Payment Page
```

## Database Schema - Payment Tables

```
tl_bookings Table
┌──────────────────────────────┐
│ EXISTING COLUMNS             │
├──────────────────────────────┤
│ booking_id (PK)              │
│ booking_reference (UNIQUE)   │
│ service_id (FK)              │
│ tourist_id (FK)              │
│ provider_id (FK)             │
│ booking_date                 │
│ service_date                 │
│ service_time                 │
│ number_of_people             │
│ original_amount              │
│ discount_amount              │
│ total_amount                 │
│ commission_amount            │
│ provider_earnings            │
│ booking_status               │
│ payment_status               │
│ special_requests             │
└──────────────────────────────┘

                │ +
┌──────────────────────────────────┐
│ PAYMENT STATUS VALUES            │
├──────────────────────────────────┤
│ payment_status:                  │
│   - 'pending' (before payment)   │
│   - 'paid' (after payment)       │
│   - 'refunded' (if cancelled)    │
└──────────────────────────────────┘

tl_payments Table
┌──────────────────────────────┐
│ EXISTING COLUMNS             │
├──────────────────────────────┤
│ payment_id (PK)               │
│ booking_id (FK)               │
│ amount                        │
│ payment_date                  │
└──────────────────────────────┘

                │ +
┌──────────────────────────────────┐
│ PAYSTACK COLUMNS                │
├──────────────────────────────────┤
│ transaction_ref (VARCHAR)        │
│ payment_method (VARCHAR)         │
│ payment_channel (VARCHAR)        │
│ authorization_code (VARCHAR)     │
│ payment_status (ENUM)            │
│ transaction_metadata (TEXT)      │
└──────────────────────────────────┘

Examples of Data:
┌─────────────────────────────────────────┐
│ Payment Record After Paystack Payment  │
├─────────────────────────────────────────┤
│ booking_id: 42                          │
│ amount: 135.45                          │
│ transaction_ref: TL-42-5-1699876543    │
│ payment_method: paystack                │
│ payment_channel: card                   │
│ authorization_code: 9g1d3y3e84x...      │
│ payment_status: successful              │
│ payment_date: 2023-11-13 14:30:00      │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Booking Record After Payment            │
├─────────────────────────────────────────┤
│ booking_id: 42                          │
│ booking_reference: TLBK-20231113-ABC123 │
│ original_amount: 150.50                 │
│ discount_amount: 15.05                  │
│ total_amount: 135.45                    │
│ commission_amount: 20.32 (15%)          │
│ provider_earnings: 115.13               │
│ payment_status: paid                    │
│ booking_status: pending                 │
└─────────────────────────────────────────┘
```

## File Dependencies

```
booking_payment.php (View)
    │
    ├─→ booking_payment.js (Client-side logic)
    │    │
    │    ├─→ validate_discount.php (Validate discount)
    │    │
    │    └─→ paystack_init_transaction.php (Initialize)
    │         │
    │         └─→ paystack_callback.php (Callback)
    │              │
    │              └─→ paystack_verify_payment.php (Verify)
    │                   │
    │                   ├─→ paystack_config.php (Config & API)
    │                   │
    │                   ├─→ booking_controller.php (Controller)
    │                   │    │
    │                   │    └─→ booking_class.php (Class)
    │                   │         │
    │                   │         └─→ db_class.php (Database)
    │                   │
    │                   ├─→ discount_controller.php (Discount)
    │                   │
    │                   └─→ service_controller.php (Service)

paystack_config.php (Config)
    └─→ core.php (Core settings)

payment_success.php (Final page)
    └─→ booking_controller.php (Get booking details)
```

## API Call Sequence

```
INITIALIZATION CALL
───────────────────

POST /actions/paystack_init_transaction.php
Content-Type: application/json

Request Body:
{
    "booking_id": 42,
    "email": "tourist@example.com",
    "discount_code": "SAVE10" (optional)
}

Response:
{
    "status": "success",
    "authorization_url": "https://checkout.paystack.com/...",
    "reference": "TL-42-5-1699876543",
    "access_code": "xxxxx",
    "amount": "135.45",
    "discount_applied": true,
    "discount_amount": "15.05",
    "message": "Redirecting to payment gateway..."
}


VERIFICATION CALL
─────────────────

POST /actions/paystack_verify_payment.php
Content-Type: application/json

Request Body:
{
    "reference": "TL-42-5-1699876543"
}

Response (Success):
{
    "status": "success",
    "verified": true,
    "message": "Payment successful! Your booking is confirmed.",
    "booking_id": 42,
    "booking_reference": "TLBK-20231113-ABC123",
    "service_title": "Kumasi Cultural Tour",
    "booking_date": "November 13, 2023",
    "amount_paid": "135.45",
    "discount_applied": true,
    "discount_amount": "15.05",
    "original_amount": "150.50",
    "currency": "GHS",
    "payment_reference": "TL-42-5-1699876543",
    "payment_method": "Card",
    "customer_email": "tourist@example.com"
}

Response (Failure):
{
    "status": "error",
    "verified": false,
    "message": "Payment verification failed: Payment status is pending"
}
```

## Payment Types Supported

```
TourLink supports two payment types:

1. BOOKING PAYMENT
   ─────────────────
   - Tourist pays for a service booking
   - Payment is recorded and booking confirmed
   - Supports discount codes
   - 15% commission deducted

2. PREMIUM SUBSCRIPTION PAYMENT
   ─────────────────────────────
   - Provider pays for premium listing
   - Monthly subscription (GH₵ 150.00)
   - Auto-renewal option
   - Activates premium status for all provider services
```

## Commission Calculation

```
Original Amount: GH₵ 150.50
Discount Applied: GH₵ 15.05
─────────────────────────────
Total Amount: GH₵ 135.45

Commission Rate: 15%
Commission Amount: GH₵ 20.32 (15% of original)
─────────────────────────────
Provider Earnings: GH₵ 115.13
```

## Discount Code Flow

```
1. Tourist enters discount code on booking_payment.php
2. JavaScript calls validate_discount.php
3. Backend validates:
   - Code exists and is active
   - Not expired
   - Usage limit not exceeded
   - Minimum amount requirement met
   - User eligibility (if user-specific)
4. Calculate discount amount
5. Return discount details to frontend
6. Update UI with discounted total
7. Include discount_code in payment initialization
8. After payment verification:
   - Record discount usage in tl_discount_usage
   - Update usage_count in tl_discount_codes
```

## File Size Summary

```
New Files:
├── settings/paystack_config.php             
├── actions/paystack_init_transaction.php    
├── actions/paystack_verify_payment.php       
├── view/paystack_callback.php                
└── view/payment_success.php

Modified Files:
├── js/booking_payment.js        (Updated)
├── view/booking_payment.php     (Updated)
├── controllers/booking_controller.php (Enhanced)
└── classes/booking_class.php (Updated)

Database Changes:
├── sql/add_paystack_payment_columns.sql
└── tl_payments table (columns added)
```

## Security Features

```
1. Session-based payment tracking
   - Payment data stored in session during flow
   - Prevents unauthorized access

2. Reference validation
   - Unique transaction references
   - Prevents duplicate payments

3. Amount verification
   - Verifies paid amount matches expected
   - Tolerance: 0.01 GHS

4. Status validation
   - Only 'success' status accepted
   - Rejects pending/failed payments

5. Booking ownership check
   - Verifies booking belongs to logged-in tourist
   - Prevents payment for others' bookings

6. Payment status check
   - Prevents double payment
   - Checks if already paid before initialization
```

## Payment Flow

```
Payment Process:
1. Tourist initiates payment → payment_status = 'pending'
2. Payment processed via Paystack → payment verified
3. Payment recorded → payment_status = 'paid'
4. Booking confirmed → booking_status = 'pending' (awaiting provider confirmation)

Payment ensures:
- Secure transaction via Paystack gateway
- Payment verification before booking confirmation
- Commission calculation (15%) for platform
- Discount code support
- Transaction reference tracking
```

