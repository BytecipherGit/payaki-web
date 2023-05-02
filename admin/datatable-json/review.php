<?php
/*
Copyright (c) 2020 Devendra Katariya (bylancer.com)
*/
require_once('includes.php');
function print_rating($rating)
{
    $rate = '';
    for ($x = 1; $x <= $rating; $x++) {
        $rate .= '<i class="fa fa-star"></i>';
    }
    if (strpos($rating, '.')) {
        $ar = explode('.', $rating, 2);
        if ($ar[1][0] <= 5) {
            $rate .= '<i class="fa fa-star-half-empty"></i>';
        } else {
            $rate .= '<i class="fa fa-star"></i>';
        }
        $x++;
    }
    while ($x <= 5) {
        $rate .= '<i class="fa fa-star-o"></i>';
        $x++;
    }
    return $rate;
}
// initilize all variable
$params = $columns = $order = $totalRecords = $data = array();
$params = $_REQUEST;
if ($params['order'][0]['column'] == 0) {
    $params['order'][0]['dir'] = "desc";
}
//define index of column
$columns = array(
    0 => 'reviewID',
    1 => 'productID',
    2 => 'comments',
    3 => 'rating',
    4 => 'date',
    5 => 'publish'
);

$where = $sqlTot = $sqlRec = "";

// check search value exist
if (!empty($params['search']['value'])) {
    if (isset($_GET['publish'])) {
        $where .= " WHERE ";
        $where .= " ( comments LIKE '" . $params['search']['value'] . "%' ";
        $where .= " AND ( publish = '".$_GET['publish']."' )";
    }else {
        $where .= " WHERE comments LIKE '%" . $params['search']['value'] . "%' ";
    }
}


// getting total number records without any search
$sql = "SELECT * FROM `" . $config['db']['pre'] . "reviews`";
$sqlTot .= $sql;
$sqlRec .= $sql;
//concatenate search sql if value exist
if (isset($where) && $where != '') {
    $sqlTot .= $where;
    $sqlRec .= $where;
}

$sqlRec .= " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'] . " LIMIT " . $params['start'] . " ," . $params['length'] . " ";
//echo $sqlRec;

$queryTot = $pdo->query($sqlTot);
$totalRecords = $queryTot->rowCount();
$queryRecords = $pdo->query($sqlRec);

//iterate on results row and create new index array of data
foreach ($queryRecords as $row) {
    $row = escape_html($row);
    $id = $row['reviewID'];
    $rating = $row['rating'];
    $product_id = $row['productID'];
    $publish = $row['publish'];
    $comments = escape_html($row['comments']);

    $info = ORM::for_table($config['db']['pre'].'user')->find_one($row['user_id']);
    $fullname = $info['name'];
    $username = $info['username'];

    $info2 = ORM::for_table($config['db']['pre'].'product')->find_one($product_id);
    $ad_title = $info2['product_name'];


    if($publish == "1"){
        $status = '<span class="label label-success">Approved</span>';
        $approved_button = "";
    }
    else{
        $status = '<span class="label label-warning">Pending</span>';
        $approved_button = '<a href="#"  class="btn btn-xs btn-success item-approve" data-ajax-action="approveReview"><i class="ion-android-done"></i></a>';
    }

    $stars = print_rating($rating);
    $row0 = '<td>
                <label class="css-input css-checkbox css-checkbox-default">
                    <input type="checkbox" class="service-checker" value="' . $id . '" id="row_' . $id . '" name="row_' . $id . '"><span></span>
                </label>
            </td>';
    $row1 = '<td><a href="post_detail.php?id='.$product_id.'">'.$ad_title.'</a><br>'.$fullname.'</td>';
    $row2 = '<td>'.$comments.'</td>';
    $row3 = '<td><div class="star-rating">'.$stars.'</div></td>';
    $row4 = '<td>'.timeAgo($row['date']).'</td>';
    $row5 = '<td>'.$status.'</td>';
    $row6 = '<td class="text-center">
                <div class="btn-group">
                    '.$approved_button.'
                    <a href="#" data-url="panel/review_edit.php?id='.$id.'" data-toggle="slidePanel"  title="Edit" class="btn btn-xs btn-default"> <i class="ion-edit"></i> </a>
                    <a href="#" title="Delete" class="btn btn-xs btn-default item-js-delete" data-ajax-action="deleteReview"><i class="ion-close"></i></a>
                </div>
            </td>';
    $value = array(
        "DT_RowId" => $id,
        0 => $row0,
        1 => $row1,
        2 => $row2,
        3 => $row3,
        4 => $row4,
        5 => $row5,
        6 => $row6
    );
    $data[] = $value;

    //print_r($value);

}

$json_data = array(
    "draw" => intval($params['draw']),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalRecords),
    "data" => $data   // total data array
);

echo json_encode($json_data);  // send data as json format
?>
