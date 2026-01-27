<?php

    function make_username_base(string $first, string $last): string {
        $first = mb_strtolower(trim($first));
        $last  = mb_strtolower(trim($last));

        $firstInitial = mb_substr($first, 0, 1);
        $base = preg_replace('/[^a-z0-9]+/u', '', $firstInitial . $last);

        if ($base === "") $base = "user";
        return $base;
    }

    function generate_unique_username(mysqli $conn, string $base): string {
        $username = $base;
        $i = 1;

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");

        while (true) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 0) {
                $stmt->close();
                return $username;
            }

            $i++;
            $username = $base . $i; // npr. ltomics2, ltomics3...
        }
    }

    function generate_password(int $length = 10): string {
        $chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@$%";
        $pass = "";
        for ($i = 0; $i < $length; $i++) {
            $pass .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pass;
    }
?>