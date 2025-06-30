<?php
// --- Database Connection ---
$conn = new mysqli('localhost', 'root', '', 'ticket');

// Check connection
if ($conn->connect_error) {
    // It's better to return a JSON error than just die() for fetch API calls
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => "Connection failed: " . $conn->connect_error]);
    exit(); // Terminate script execution
}

// --- Function to Get Approved Records ---
function getAllApprovedRecords($conn) {
    $records = [];
    $errorMessages = []; // To collect potential errors from individual queries

    // --- ICT Technical Assistance ---
    // IMPORTANT: Assumes a column named 'status' exists and 'Approved' is the value for approved requests.
    $sql_ict = "SELECT 'ICT Technical Assistance' as request_type,
                       id as request_id,
                       requester_name as requestor,
                       department as office_school,
                       CONCAT(assistance, ' - ', COALESCE(description, '')) as request_details,
                       date_reported as date_requested,
                       date_completed
                FROM icttechnicalassistance
                WHERE status = 'Approved'"; // Filter by status

    $result_ict = $conn->query($sql_ict);
    if ($result_ict) {
        while ($row = $result_ict->fetch_assoc()) {
            $records[] = $row;
        }
        $result_ict->free(); // Free result set
    } else {
        $errorMessages[] = "Error querying ICT Technical Assistance: " . $conn->error;
    }

    // --- DTS Request ---
    // IMPORTANT: Assumes a column named 'status' exists.
    $sql_dts = "SELECT 'DTS Request' as request_type,
                       id as request_id,
                       requesterName as requestor,
                       school as office_school,
                       CONCAT(requestType, ' - ', COALESCE(reason, ''), ' ', COALESCE(editReason, ''), ' ', COALESCE(cancelReason, ''), ' ', COALESCE(emailAddress, ''), COALESCE(documentType, ''), COALESCE(newEmail, '')) as request_details,
                       date as date_requested,
                       date_completed
                FROM dtsrequest
                WHERE status = 'Approved'"; // Filter by status

    $result_dts = $conn->query($sql_dts);
    if ($result_dts) {
        while ($row = $result_dts->fetch_assoc()) {
            $records[] = $row;
        }
         $result_dts->free();
    } else {
        $errorMessages[] = "Error querying DTS Request: " . $conn->error;
    }

    // --- Email Request ---
    // IMPORTANT: Assumes a column named 'status' exists.
    $sql_email = "SELECT 'Email Request' as request_type,
                         id as request_id,
                         full_name as requestor,
                         school_name as office_school,
                         request_type as request_details, -- Assuming 'request_type' here holds the detail like 'New', 'Password Reset'
                         date_filed as date_requested,
                         date_completed
                  FROM emailrequest
                  WHERE status = 'Approved'"; // Filter by status

    $result_email = $conn->query($sql_email);
    if ($result_email) {
        while ($row = $result_email->fetch_assoc()) {
            $records[] = $row;
        }
         $result_email->free();
    } else {
        $errorMessages[] = "Error querying Email Request: " . $conn->error;
    }

    // --- Help Desk ---
    // IMPORTANT: Assumes a column named 'status' exists.
    $sql_helpdesk = "SELECT 'Help Desk' as request_type,
                            id as request_id,
                            requesting_official_name as requestor,
                            requesting_official_office as office_school,
                            details_request as request_details,
                            date_filed as date_requested,
                            date_completed
                     FROM helpdesk
                     WHERE status = 'Approved'"; // Filter by status

    $result_helpdesk = $conn->query($sql_helpdesk);
    if ($result_helpdesk) {
        while ($row = $result_helpdesk->fetch_assoc()) {
            $records[] = $row;
        }
         $result_helpdesk->free();
    } else {
        $errorMessages[] = "Error querying Help Desk: " . $conn->error;
    }

    // --- Sort Records by Date Requested (Ascending) ---
    // This handles cases where date_requested might be null or invalid slightly better
    usort($records, function($a, $b) {
        $dateA = isset($a['date_requested']) ? strtotime($a['date_requested']) : false;
        $dateB = isset($b['date_requested']) ? strtotime($b['date_requested']) : false;

        if ($dateA === false && $dateB === false) return 0; // Both invalid/missing
        if ($dateA === false) return 1;  // Treat invalid A as greater (comes later)
        if ($dateB === false) return -1; // Treat invalid B as greater

        // If both are valid, compare timestamps
        return $dateA - $dateB;
    });

    // Return both records and any potential errors
    return ['data' => $records, 'errors' => $errorMessages];
}

// --- Execute and Output JSON ---
$resultData = getAllApprovedRecords($conn);

header('Content-Type: application/json');

// Check if there were any SQL errors during fetching
if (!empty($resultData['errors'])) {
    // Log errors server-side if desired
    // error_log(implode("\n", $resultData['errors']));

    // Optionally, you might still return partial data or just the error
    // For simplicity here, return success=false if any error occurred
     echo json_encode(['success' => false, 'error' => 'Errors occurred during data retrieval.', 'details' => $resultData['errors']]);
} else {
    // No errors, return the data
    echo json_encode(['success' => true, 'data' => $resultData['data']]);
}

// --- Close Connection ---
$conn->close();
?>