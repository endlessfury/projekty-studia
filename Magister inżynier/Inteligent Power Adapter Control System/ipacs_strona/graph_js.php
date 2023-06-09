<?php
    require_once 'session_login.php';
?>
<?php
                            function js_str($s)
                            {
                                return '"' . addcslashes($s, "\0..\37\"\\") . '"';
                            }

                            function js_array($array)
                            {
                                $temp = array_map('js_str', $array);
                                return '[' . implode(',', $temp) . ']';
                            }

                            
                        ?>