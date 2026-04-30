<?php
    // session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include('config.php');
    
    $action = isset($_POST['action']) && !empty($_POST['action']) ? $_POST['action'] : '';
    if(empty($action)) 
    {
        $action = isset($_GET['action']) && !empty($_GET['action']) ? $_GET['action'] : '';
    }
    
    // echo '<pre>'; print_r($_POST['action']); exit;
    if($action == 'form_save') 
    {
        $mysqli->begin_transaction();
        try
        {
            $hotel_name = isset($_POST['hotel_name']) ? trim($_POST['hotel_name']) : '';
            $description = isset($_POST['hotel_description']) ? trim($_POST['hotel_description']) : '';
            $star_rating = isset($_POST['star_rating']) ? (int)$_POST['star_rating'] : 0;
            $status = isset($_POST['status']) ? trim($_POST['status']) : '';
            
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            $city = isset($_POST['city']) ? trim($_POST['city']) : '';
            $state = isset($_POST['state']) ? trim($_POST['state']) : '';
            $country = isset($_POST['country']) ? trim($_POST['country']) : '';
            $zipcode = isset($_POST['zipcode']) ? trim($_POST['zipcode']) : '';
            $latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : 0.0;
            $longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : 0.0;
            
            $phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
            $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
            $website = isset($_POST['website_url']) ? filter_var(trim($_POST['website_url']), FILTER_SANITIZE_URL) : '';
            $contact_person = isset($_POST['contact_person']) ? trim($_POST['contact_person']) : '';
            
            $errors = [];

            if (empty($hotel_name)) 
            {
                $errors[] = 'Hotel name is required.';
            }
            // if (empty($address)) 
            // {
            //     $errors[] = 'Address is required.';
            // }
            // if (empty($city)) 
            // {
            //     $errors[] = 'City is required.';
            // }
          
            
            
            // if (empty($country)) 
            // {
            //     $errors[] = 'Country is required.';
            // }
            if (empty($phone)) 
            {
                $errors[] = 'Phone number is required.';
            }

            if (!empty($errors)) 
            {
                $_SESSION['status'] = '400';
                $_SESSION['data'] = '';
                $_SESSION['message'] = implode('<br>', $errors);
                header('Location: hotels.php');
                exit();
            }
                        
            $sql = "INSERT INTO `hotels` (`hotel_name`, `hotel_description`, `star_rating`, `address`, `city`, `state`, `country`, `zipcode`, `latitude`, `longitude`, `phone_number`, `email`, `website_url`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ssissssssssss", $hotel_name, $description, $star_rating, $address, $city, $state, $country, $zipcode, $latitude, $longitude, $phone, $email, $website);
            
            if($stmt->execute()) 
            {
                $hotel_id = $mysqli->insert_id;
                if(isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) 
                {
                    $uploadDir = 'uploads/hotels/';
                    if (!file_exists($uploadDir)) 
                    {
                        if (!mkdir($uploadDir, 0755, true)) 
                        {
                            $_SESSION['status'] = '400';
                            $_SESSION['message'] = 'Failed to create upload directory';
                            header('Location: hotels.php');
                            exit();
                        }
                    }
                    
                    if (!is_writable($uploadDir)) 
                    {
                        $_SESSION['status'] = '400';
                        $_SESSION['message'] = 'Upload directory is not writable';
                        header('Location: hotels.php');
                        exit();
                    }
                    
                    foreach($_FILES['photos']['tmp_name'] as $key => $tmp_name) 
                    {
                        if($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) 
                        {
                            $fileName = basename($_FILES['photos']['name'][$key]);
                            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 'webp', 'ico', 'heic', 'heif', 'svg'];
                            
                            if(in_array($fileExt, $allowed)) 
                            {
                                $newFileName = uniqid('hotel_', true).'.'.$fileExt;
                                $fileDestination = $uploadDir . $newFileName;
                                
                                if(move_uploaded_file($tmp_name, $fileDestination)) 
                                {
                                    $relativePath = 'uploads/hotels/' . $newFileName;
                                    $photo_sql = "INSERT INTO `photos` (`hotel_id`, `photo_url`) VALUES (?, ?)";
                                    $photo_stmt = $mysqli->prepare($photo_sql);
                                    $photo_stmt->bind_param("is", $hotel_id, $relativePath);
                                    if (!$photo_stmt->execute()) 
                                    {
                                        $_SESSION['status'] = '400';
                                        $_SESSION['data'] = '';
                                        $_SESSION['message'] = "Failed to save photo record: " . $mysqli->error;

                                        header('Location: hotels.php');
                                        exit();
                                    }
                                } 
                                else 
                                {     
                                    $_SESSION['status'] = '400';
                                    $_SESSION['data'] = '';
                                    $_SESSION['message'] = "Failed to move uploaded file to: " . $fileDestination;

                                    header('Location: hotels.php');
                                    exit();                        
                                }
                            }
                            else
                            {
                                $_SESSION['status'] = '400';
                                $_SESSION['data'] = '';
                                $_SESSION['message'] =  "The file type of $fileName is not allowed.";

                                header('Location: hotels.php');
                                exit(); 
                            }
                        } 
                        else 
                        {
                            $_SESSION['status'] = '400';
                            $_SESSION['data'] = '';
                            $_SESSION['message'] = "Upload error for file $key: " . $_FILES['photos']['error'][$key];

                            header('Location: hotels.php');
                            exit(); 
                        }
                    }
                }
                
                if(isset($_POST['room_types']) && !empty($_POST['room_types'][0])) 
                {
                    $room_types = $_POST['room_types'];
                    $room_prices = isset($_POST['room_prices']) ? $_POST['room_prices'] : [];
                    
                    for($i = 0; $i < count($room_types); $i++) 
                    {
                        if(!empty($room_types[$i]) && isset($room_prices[$i])) 
                        {
                            $room_sql = "INSERT INTO `rooms` (`hotel_id`, `room_type`, `room_price`) VALUES (?, ?, ?)";
                            $room_stmt = $mysqli->prepare($room_sql);
                            $room_stmt->bind_param("isd", $hotel_id, $room_types[$i], $room_prices[$i]);
                            $room_stmt->execute();
                        }
                    }
                }

                if(isset($_POST['amenities']) && !empty($_POST['amenities'])) 
                {
                    foreach($_POST['amenities'] as $amenity) 
                    {
                        if(!empty($amenity)) 
                        {
                            $amenity_sql = "INSERT INTO `amenities` (`hotel_id`, `amenity_name`) VALUES (?, ?)";
                            $amenity_stmt = $mysqli->prepare($amenity_sql);
                            $amenity_stmt->bind_param("is", $hotel_id, $amenity);
                            $amenity_stmt->execute();
                        }
                    }
                }

                if((isset($_POST['check_in_time']) && !empty($_POST['check_in_time'])) || 
                (isset($_POST['check_out_time']) && !empty($_POST['check_out_time']))) 
                {
                    $check_in = isset($_POST['check_in_time']) ? $_POST['check_in_time'] : '00:00:00';
                    $check_out = isset($_POST['check_out_time']) ? $_POST['check_out_time'] : '00:00:00';
                    $cancellation_policy = isset($_POST['cancellation_policy']) ? $_POST['cancellation_policy'] : '';
                    $policy_sql = "INSERT INTO `policies` (`hotel_id`, `check_in_time`, `check_out_time`, `cancellation_policy`) VALUES (?, ?, ?, ?)";
                    $policy_stmt = $mysqli->prepare($policy_sql);
                    $policy_stmt->bind_param("iss", $hotel_id, $check_in, $check_out, $cancellation_policy);
                    $policy_stmt->execute();
                }

                if(isset($_POST['meal_types']) && !empty($_POST['meal_types'][0])) 
                {
                    $meal_types = $_POST['meal_types'];
                    $meal_plan = isset($_POST['meal_plan']) ? $_POST['meal_plan'] : [];
                    $meal_prices = isset($_POST['meal_prices']) ? $_POST['meal_prices'] : [];
                    $meal_descriptions = isset($_POST['meal_descriptions']) ? $_POST['meal_descriptions'] : [];
                    for($i = 0; $i < count($meal_types); $i++) 
                    {
                        if(!empty($meal_types[$i])) 
                        {
                            $price = isset($meal_plan[$i]) ? $meal_plan[$i] : 0;
                            $desc = isset($meal_descriptions[$i]) ? $meal_descriptions[$i] : '';
                            $meal_sql = "INSERT INTO `meals` (`hotel_id`, `meal_type`, `meal_plan`, `meal_plan_price`, `description`) VALUES (?, ?, ?, ?, ?)";
                            $meal_stmt = $mysqli->prepare($meal_sql);
                            $meal_stmt->bind_param("isiis", $hotel_id, $meal_types[$i], $meal_plan[$i], $meal_prices[$i], $desc);
                            $meal_stmt->execute();
                        }
                    }
                }
                
                $_SESSION['status'] = '200';
                $_SESSION['data'] = '';
                $_SESSION['message'] = 'Hotel added successfully!';

                $mysqli->commit();
                header('Location: hotels.php');
                exit();
            } 
            else 
            {
                $_SESSION['status'] = '400';
                $_SESSION['data'] = '';
                $_SESSION['message'] = 'Error adding hotel: ' . $mysqli->error;

                header('Location: hotels.php');
                exit();
            }
        }
        catch (Exception $e)
        {
            $mysqli->rollback(); 
            $_SESSION['status'] = '500';
            $_SESSION['message'] = $e->getMessage();
        }
        header('Location: hotels.php');
        exit();
    }

    elseif($action == 'update_hotel') 
    {
        $mysqli->begin_transaction();
        try
        {
            $hotel_id = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : 0;
            if($hotel_id <= 0) 
            {
                $_SESSION['status'] = '400';
                $_SESSION['data'] = '';
                $_SESSION['message'] = 'Invalid hotel ID';
                header('Location: view-hotel?id='.$hotel_id);
                exit();
            }
            
            $hotel_name = isset($_POST['hotel_name']) ? trim($_POST['hotel_name']) : '';
            $description = isset($_POST['hotel_description']) ? trim($_POST['hotel_description']) : '';
            $star_rating = isset($_POST['star_rating']) ? (int)$_POST['star_rating'] : 0;
            $status = isset($_POST['status']) ? trim($_POST['status']) : '';
            
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            $city = isset($_POST['city']) ? trim($_POST['city']) : '';
            $state = isset($_POST['state']) ? trim($_POST['state']) : '';
            $country = isset($_POST['country']) ? trim($_POST['country']) : '';
            $zipcode = isset($_POST['zipcode']) ? trim($_POST['zipcode']) : '';
            $latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : 0.0;
            $longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : 0.0;
            
            $phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
            $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
            $website = isset($_POST['website_url']) ? filter_var(trim($_POST['website_url']), FILTER_SANITIZE_URL) : '';

            $errors = [];
            if (empty($hotel_name)) $errors[] = 'Hotel name is required.';
            // if (empty($address)) $errors[] = 'Address is required.';
            // if (empty($city)) $errors[] = 'City is required.';
            // if (empty($state)) $errors[] = 'State is required.';
            // if (empty($country)) $errors[] = 'Country is required.';
            if (empty($phone)) $errors[] = 'Phone number is required.';
            
            if (!empty($errors)) 
            {
                $_SESSION['status'] = '400';
                $_SESSION['data'] = '';
                $_SESSION['message'] = implode('<br>', $errors);
                header('Location: view-hotel?id='.$hotel_id);
                exit();
            }
            
            $sql = "UPDATE `hotels` SET `hotel_name` = ?, `hotel_description` = ?, `star_rating` = ?, `status` = ?, 
            `address` = ?, `city` = ?, `state` = ?, `country` = ?, `zipcode` = ?, `latitude` = ?, `longitude` = ?, 
            `phone_number` = ?, `email` = ? WHERE `hotel_id` = ?";
            $stmt = $mysqli->prepare($sql);

            $stmt->bind_param(
                "ssissssssssssi", 
                $hotel_name, $description, $star_rating, $status,
                $address, $city, $state, $country, $zipcode, 
                $latitude, $longitude, $phone, $email, $hotel_id
            );
            
            if(!$stmt->execute()) 
            {
                $_SESSION['status'] = '400';
                $_SESSION['data'] = '';
                $_SESSION['message'] = "Failed to update hotel: " . $mysqli->error;
                header('Location: view-hotel?id='.$hotel_id);
                exit();
            }
            
            if(isset($_POST['room_ids'])) 
            {
                $room_ids = $_POST['room_ids'];
                $room_types = $_POST['room_types'];
                $room_prices = $_POST['room_prices'];
                
                for($i = 0; $i < count($room_ids); $i++) 
                {
                    if($room_ids[$i] == 'new') 
                    {
                        $sql = "INSERT INTO `rooms` (`hotel_id`, `room_type`, `room_price`) VALUES (?, ?, ?)";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("isd", $hotel_id, $room_types[$i], $room_prices[$i]);
                        $stmt->execute();
                    } 
                    else 
                    {
                        $sql = "UPDATE `rooms` SET `room_type` = ?, `room_price` = ? WHERE `room_id` = ? AND `hotel_id` = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("sdii", $room_types[$i], $room_prices[$i], $room_ids[$i], $hotel_id);
                        $stmt->execute();
                    }
                }
            }
            
            if(isset($_POST['amenity_ids'])) 
            {
                $amenity_ids = $_POST['amenity_ids'];
                $amenity_names = $_POST['amenity_names'];
                $sql = "DELETE FROM `amenities` WHERE `hotel_id` = ?";
                if(!empty($amenity_ids)) 
                {
                    $placeholders = implode(',', array_fill(0, count($amenity_ids), '?'));
                    $sql .= " AND `amenity_id` NOT IN ($placeholders)";
                }
                
                $stmt = $mysqli->prepare($sql);
                if(!empty($amenity_ids)) 
                {
                    $types = str_repeat('i', count($amenity_ids));
                    $stmt->bind_param("i$types", $hotel_id, ...$amenity_ids);
                } 
                else 
                {
                    $stmt->bind_param("i", $hotel_id);
                }
                $stmt->execute();
                for($i = 0; $i < count($amenity_ids); $i++) 
                {
                    if($amenity_ids[$i] == 'new') 
                    {
                        $sql = "INSERT INTO `amenities` (`hotel_id`, `amenity_name`) VALUES (?, ?)";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("is", $hotel_id, $amenity_names[$i]);
                        $stmt->execute();
                    } 
                    else 
                    {
                        $sql = "UPDATE `amenities` SET `amenity_name` = ? WHERE `amenity_id` = ? AND `hotel_id` = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("sii", $amenity_names[$i], $amenity_ids[$i], $hotel_id);
                        $stmt->execute();
                    }
                }
            }
            
            if(isset($_POST['meal_ids'])) 
            {
                $meal_ids = $_POST['meal_ids'];
                $meal_plans = $_POST['meal_plans'];
                $meal_descriptions = $_POST['meal_descriptions'];

                $sql = "DELETE FROM `meals` WHERE `hotel_id` = ?";
                if(!empty($meal_ids)) 
                {
                    $placeholders = implode(',', array_fill(0, count($meal_ids), '?'));
                    $sql .= " AND `meal_id` NOT IN ($placeholders)";
                }
                
                $stmt = $mysqli->prepare($sql);
                if(!empty($meal_ids)) 
                {
                    $types = str_repeat('i', count($meal_ids));
                    $stmt->bind_param("i$types", $hotel_id, ...$meal_ids);
                } 
                else 
                {
                    $stmt->bind_param("i", $hotel_id);
                }
                $stmt->execute();
                
                for($i = 0; $i < count($meal_ids); $i++) 
                {
                    if($meal_ids[$i] == 'new') 
                    {
                        $sql = "INSERT INTO `meals` (`hotel_id`, `meal_type`, `description`) VALUES (?, ?, ?)";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("iss", $hotel_id, $meal_plans[$i], $meal_descriptions[$i]);
                        $stmt->execute();
                    } 
                    else 
                    {
                        $sql = "UPDATE `meals` SET `meal_type` = ?, `description` = ? WHERE `meal_id` = ? AND `hotel_id` = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("ssii", $meal_plans[$i], $meal_descriptions[$i], $meal_ids[$i], $hotel_id);
                        $stmt->execute();
                    }
                }
            }

            $check_in = isset($_POST['check_in_time']) ? $_POST['check_in_time'] : null;
            $check_out = isset($_POST['check_out_time']) ? $_POST['check_out_time'] : null;
            $cancellation_policy = isset($_POST['cancellation_policy']) ? $_POST['cancellation_policy'] : '';
        
            $sql = "SELECT COUNT(*) FROM `policies` WHERE `hotel_id` = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("i", $hotel_id);
            $stmt->execute();
            $stmt->bind_result($policy_count);
            $stmt->fetch();
            $stmt->close();
            
            if($policy_count > 0) 
            {
                $sql = "UPDATE `policies` SET `check_in_time` = ?, `check_out_time` = ?, `cancellation_policy` = ? WHERE `hotel_id` = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("sssi", $check_in, $check_out, $cancellation_policy, $hotel_id);
                $stmt->execute();
            } 
            else 
            {
                $sql = "INSERT INTO `policies` (`hotel_id`, `check_in_time`, `check_out_time`, `cancellation_policy`) VALUES (?, ?, ?, ?)";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("isss", $hotel_id, $check_in, $check_out, $cancellation_policy);
                $stmt->execute();
            }
        
            if(isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) 
            {
                $uploadDir = 'uploads/hotels/';
                if (!file_exists($uploadDir)) 
                {
                    if (!mkdir($uploadDir, 0755, true)) 
                    {
                        $_SESSION['status'] = '400';
                        $_SESSION['data'] = '';
                        $_SESSION['message'] = 'Failed to create upload directory';
                        header('Location: view-hotel?id='.$hotel_id);
                        exit();
                    }
                }
                
                foreach($_FILES['photos']['tmp_name'] as $key => $tmp_name) 
                {
                    if($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) 
                    {
                        $fileName = basename($_FILES['photos']['name'][$key]);
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if(in_array($fileExt, $allowed)) 
                        {
                            $newFileName = uniqid('hotel_', true).'.'.$fileExt;
                            $fileDestination = $uploadDir . $newFileName;
                            
                            if(move_uploaded_file($tmp_name, $fileDestination)) 
                            {
                                $relativePath = 'uploads/hotels/' . $newFileName;
                                $photo_sql = "INSERT INTO `photos` (`hotel_id`, `photo_url`) VALUES (?, ?)";
                                $photo_stmt = $mysqli->prepare($photo_sql);
                                $photo_stmt->bind_param("is", $hotel_id, $relativePath);
                                $photo_stmt->execute();
                            }
                        }
                    }
                }
            }
            
            $mysqli->commit();
            
            $_SESSION['status'] = '200';
            $_SESSION['message'] = 'Hotel updated successfully!';
            header('Location: view-hotel.php?id='.$hotel_id);
            exit();
        }
        catch (Exception $e) 
        {
            $mysqli->rollback();
            $_SESSION['status'] = '500';
            $_SESSION['message'] = $e->getMessage();
            header('Location: hotel_details.php?id='.$hotel_id);
            exit();
        }
    }

    elseif($action == 'delete_photo') 
    {
        $photo_id = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0;
        
        if($photo_id <= 0) 
        {
            echo json_encode(['success' => false, 'message' => 'Invalid photo ID']);
            exit;
        }
        $sql = "SELECT `photo_url` FROM `photos` WHERE `photo_id` = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        $stmt->bind_result($photo_url);
        $stmt->fetch();
        $stmt->close();
        
        if(!empty($photo_url)) 
        {
            if(file_exists($photo_url)) 
            {
                unlink($photo_url);
            }
            $sql = "DELETE FROM `photos` WHERE `photo_id` = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("i", $photo_id);
            
            if($stmt->execute()) 
            {
                echo json_encode(['success' => true]);
            } 
            else 
            {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } 
        else 
        {
            echo json_encode(['success' => false, 'message' => 'Photo not found']);
        }
        exit;
    }


    if ($action=== 'delete_user') 
    {
        try 
        {
            $id = intval($_POST['id']);
            $stmt = $mysqli->prepare("DELETE FROM `users` WHERE `id` = ?");
            if (!$stmt) 
            {
                $res = array(
                    'status' => 200,
                    'data' => '',
                    'message' => "Prepare statement failed:" 
                );
            }
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) 
            {
                $res = array(
                    'status' => 200,
                    'data' => '',
                    'message' => "User deleted successfully"
                );
            } 
            else 
            {
                $res = array(
                    'status' => 400,
                    'data' => '',
                    'message' => "Failed to delete user"
                );
            }
            $stmt->close();
            $mysqli->close();
        } 
        catch (Exception $error) 
        {
            $res = array(
                'status' => 500,
                'data' => '',
                'message' => "Internal server error: " . $error->getMessage()
            );
        }
        echo json_encode($res);
        exit;
    }
?>