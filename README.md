# Noteq_AbandonedCart Magento 2 Extension

The `Noteq_AbandonedCart` extension for Magento 2 is designed to help store owners recover potentially lost sales by sending automated reminder emails to customers who have abandoned their carts. It includes a configurable abandonment period and provides detailed insights into abandoned cart activity through an admin grid.

## Features

- Automatically sends reminder emails for abandoned carts.
- Abandonment time period is configurable from the Magento admin panel.
- Includes an admin grid that lists notified customers.
- Displays relevant cart details including:
  - Cart ID
  - Total amount
  - Total number of items
- Provides an admin action button to manually resend emails to selected customers.

## Installation

1. Place the module in the following directory:
app/code/Noteq/AbandonedCart

2. Enable the module and update the Magento setup:

```bash
bin/magento module:enable Noteq_AbandonedCart
bin/magento setup:upgrade
bin/magento cache:flush
```

![image](https://github.com/user-attachments/assets/3ac834c3-2ce5-49a4-bb5d-896aab80a6ab)
![image](https://github.com/user-attachments/assets/c15f5e7c-bab0-4f63-9237-b41e78ab08aa)

