Autoreorder Module
The Autoreorder module for Magento 2 allows customers to set up automatic reorder of their preferred products at specified intervals.

## Features
- **Automated Reorder**: Customers can easily configure and schedule automatic reorder of products they frequently purchase.
- **Flexible Interval Options**: Customers can choose from a range of interval options, 

## Installation

To install the Autoreorder module via Composer, follow these steps:
1. Ensure you have a working Magento 2 instance.
2. Open a terminal or command prompt.
3. Navigate to the root directory of your Magento installation.
4. Run the following command to require the Autoreorder module:

   ```bash
   composer require osio/module-subscriptions
   ```
   
5. Wait for Composer to download and install the module and its dependencies.

## Configuration

To configure the Autoreorder module, follow these steps:

1. Log in to your Magento admin panel.
2. Go to **Stores** > **Configuration** > **System**. > **Product Subscriptions**
Here you set general intervals
3. Save the configuration changes
4. Edit the Backend Product Page and mark the product by checking the attribute Subscribable.
5. Under the options of that product you set individual intervals to reorder 


## Usage

Customers can utilize the autoreorder feature by following these steps:

1. Browse to the product page of the item they want to autoreorder.
3. Select the desired reorder period from the pull-down menu. This sets the interval at which the product will be automatically reordered.
4. Add the Product to the cart for a first order

## Support
If you encounter any issues or have questions related to the Autoreorder module, please reach out

## Contributing
We welcome contributions to enhance the functionality or address any bugs in the Autoreorder module. 

## License
The Autoreorder module is released under the MIT License