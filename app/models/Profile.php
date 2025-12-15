<?php

class Profile extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /*
       GET PROFILE BY USER ID
    */
    public function getByUserId($userId)
    {
        $userId = (int)$userId;

        $sql = "SELECT * FROM profile WHERE user_id = $userId LIMIT 1";
        $res = mysqli_query($this->db, $sql);

        if (!$res || mysqli_num_rows($res) === 0) {
            return null;
        }

        $row = mysqli_fetch_assoc($res);

        return [
            "full_name" => $row["full_name"],
            "dob"       => $row["DOB"],      
            "address"   => $row["Address"],  
            "phone"     => $row["phone"]
        ];
    }

    /*
       UPDATE OR INSERT PROFILE
    */
    public function updateProfile($userId, $full_name, $dob, $address, $phone)
    {
        $userId = (int)$userId;

        // Escape values
        $full_nameEsc = mysqli_real_escape_string($this->db, $full_name);
        $dobEsc       = mysqli_real_escape_string($this->db, $dob);
        $addressEsc   = mysqli_real_escape_string($this->db, $address);
        $phoneEsc     = mysqli_real_escape_string($this->db, $phone);

        // Check if profile exists
        $sqlCheck = "SELECT id FROM profile WHERE user_id = $userId LIMIT 1";
        $resCheck = mysqli_query($this->db, $sqlCheck);

        if ($resCheck && mysqli_num_rows($resCheck) > 0) {
            // UPDATE
            $row        = mysqli_fetch_assoc($resCheck);
            $profileId  = (int)$row['id'];

            $sql = "
                UPDATE profile SET
                    full_name = '$full_nameEsc',
                    DOB       = '$dobEsc',
                    Address   = '$addressEsc',
                    phone     = '$phoneEsc'
                WHERE id = $profileId
                LIMIT 1
            ";
        } else {
            // INSERT
            $sql = "
                INSERT INTO profile (user_id, full_name, DOB, phone, Address)
                VALUES ($userId, '$full_nameEsc', '$dobEsc', '$phoneEsc', '$addressEsc')
            ";
        }

        $res = mysqli_query($this->db, $sql);

        if (!$res) {
            die("Profile update error: " . mysqli_error($this->db));
        }

        return true;
    }
}
