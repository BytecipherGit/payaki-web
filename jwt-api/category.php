<?php
class Category
{
    private $cat_id;
    private $cat_order;
    private $cat_name;
    private $slug;
    private $icon;
    private $picture;
    private $tableName = 'ad_catagory_main';
    private $dbConn;

    public function setCatId($cat_id)
    {$this->cat_id = $cat_id;}
    public function getCatId()
    {return $this->cat_id;}
    public function setCatOrder($cat_order)
    {$this->cat_order = $cat_order;}
    public function getCatOrder()
    {return $this->cat_order;}
    public function setCatName($cat_name)
    {$this->cat_name = $cat_name;}
    public function getCatName()
    {return $this->cat_name;}
    public function setSlug($slug)
    {$this->slug = $slug;}
    public function getSlug()
    {return $this->slug;}
    public function setIcon($icon)
    {$this->icon = $icon;}
    public function getIcon()
    {return $this->icon;}
    public function setPicture($picture)
    {$this->picture = $picture;}
    public function getPicture()
    {return $this->picture;}

    public function __construct()
    {
        $db = new DbConnect();
        $this->dbConn = $db->connect();
    }

    public function getAllCategories()
    {
        $stmt = $this->dbConn->prepare("SELECT * FROM " . $this->tableName." ORDER BY cat_order ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $categories;
    }

    public function getCategoryDetailsById()
    {

        $sql = "SELECT c.* FROM ad_catagory_main c WHERE c.main_cat_id = :categoryId";
        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindParam(':categoryId', $this->cat_id);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        return $customer;
    }

    public function getSubCategoryListingById()
    {

        // $sql = "SELECT c.* FROM ad_catagory_sub c LEFTJOIN ad_catagory_main u ON (c.main_cat_id = u.cat_id) WHERE c.main_cat_id = :categoryId";
		$sql = "SELECT acm.*,acs.* FROM ad_catagory_main AS acm JOIN ad_catagory_sub AS acs ON acm.cat_id = acs.main_cat_id WHERE acm.cat_id= :categoryId";
        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindParam(':categoryId', $this->cat_id);
        $stmt->execute();
        $customer = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $customer;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ' . $this->tableName . '(cat_id, cat_order, cat_name, slug, icon, picture) VALUES(null, :cat_order, :cat_name, :slug, :icon, :picture)';
        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindParam(':cat_order', $this->cat_order);
        $stmt->bindParam(':cat_name', $this->cat_name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':icon', $this->icon);
        $stmt->bindParam(':picture', $this->picture);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update()
    {
        $sql = "UPDATE $this->tableName SET";
        if (null != $this->getCatOrder()) {
            $sql .= " cat_order = '" . $this->getCatOrder() . "',";
        }
        if (null != $this->getCatName()) {
            $sql .= " cat_name = '" . $this->getCatName() . "',";
        }
        if (null != $this->getSlug()) {
            $sql .= " slug = " . $this->getSlug() . ",";
        }
        if (null != $this->getIcon()) {
            $sql .= " icon = " . $this->getIcon() . ",";
        }
        if (null != $this->getPicture()) {
            $sql .= " picture = " . $this->getPicture() . ",";
        }
        $sql .= " WHERE cat_id = :catId";
        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindParam(':catId', $this->cat_id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete()
    {
        $stmt = $this->dbConn->prepare('DELETE FROM ' . $this->tableName . ' WHERE cat_id = :catId');
        $stmt->bindParam(':catId', $this->cat_id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
