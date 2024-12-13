<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the webhook payload from Paystack
$input = @file_get_contents("php://input");
$event = json_decode($input);
error_log(print_r($event, true)); // This will log the entire event to the error log for inspection
// Database connection details
$servername = "localhost";
$username = "skwankwa_Luke";
$password = "OinGun71!";
$dbname = "skwankwa_Basket";

// Check if the event object is valid
if ($event) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Handle successful payment event
    if ($event->event == "charge.success") {
        // Extract payment details
        $reference = $event->data->reference;
        $amount = $event->data->amount / 100; // Paystack amount is in kobo
        $email = $event->data->customer->email;

        $delivery_address = "No address provided"; // Default value

        // Check if custom fields exist and extract delivery address
        if (isset($event->data->metadata->custom_fields) && is_array($event->data->metadata->custom_fields)) {
            foreach ($event->data->metadata->custom_fields as $field) {
                if ($field->variable_name === "delivery_address") {
                    $delivery_address = $field->value;
                    break;
                }
            }
        }

        // Insert payment details into Payments table
        $sql = "INSERT INTO Payments (reference, amount, email, delivery_address) VALUES ('$reference', '$amount', '$email', '$delivery_address')";
        if ($conn->query($sql) === TRUE) {
            http_response_code(200); // Payment logged successfully
        } else {
            http_response_code(500); // Database error
            error_log("SQL error (inserting payment): " . $conn->error);
        }
    }

    // Close the database connection
    $conn->close();
}
?>
