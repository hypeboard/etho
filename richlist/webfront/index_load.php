
<?php
  $config = parse_ini_file('/var/www/conf/richlist/settings.ini'); 
  // Create connection
  $conn = new mysqli($config['servername'],$config['username'],$config['password'],$config['dbname']);
  $table_columns = array("address" => "1", "value" => "2", "numIn" => "6", "numOut" => "9");

  // Check connection
  if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
  } 
  else 
  {
    $record_start = intval($_GET['start']);
    $record_limit = intval($_GET['length']);
    
    // main query for the actuall table data
    $sql = "SELECT * FROM richlist ORDER BY value DESC LIMIT $record_limit OFFSET $record_start";
    $result = $conn->query($sql);

    // the current circulating supply of ETHO
    $supply = $conn->query("SELECT sum(value) as 'supply' FROM richlist;");
    $supply = $supply->fetch_assoc();

    // count of total rows in the richlist table
    $count = $conn->query("SELECT count(*) as 'total_rows' FROM richlist");
    $count = $count->fetch_assoc();


    $table_data = (object) 
    [
      'draw' => $_GET['draw'],
      'recordsTotal' => $count["total_rows"],
      'recordsFiltered' => $count["total_rows"]
    ];

    if ($result->num_rows > 0) 
    {
      $rows_data = array();

      // output data of each row
      while($row = $result->fetch_assoc()) 
      {
        $addrvalue = floatval($row["value"]) / pow(10,18);
        $addrpercent = $addrvalue / (floatval($supply["supply"]) / pow(10,18));

        $row_data = array();
        array_push($row_data, $row["id"]);
        array_push($row_data, $row["address"]);
        array_push($row_data, number_format($addrvalue,2)); 
        array_push($row_data, number_format($addrpercent,2)); 
        array_push($row_data, $row["firstIn"]);
        array_push($row_data, $row["lastIn"]);
        array_push($row_data, $row["numIn"]);
        array_push($row_data, $row["firstOut"]);
        array_push($row_data, $row["lastOut"]);
        array_push($row_data, $row["numOut"]);
        array_push($rows_data, $row_data);
      }
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // return the table data
    $table_data->data = $rows_data;
    echo json_encode($table_data);
  }
  $conn->close();   
?>