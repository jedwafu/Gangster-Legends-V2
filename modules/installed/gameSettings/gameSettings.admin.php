<?php

	class adminModule {

		private function getRank($rankID = "all") {
			if ($rankID == "all") {
				$add = "";
			} else {
				$add = " WHERE R_id = :id";
			}
			
			$rank = $this->db->prepare("
				SELECT
					R_id as 'id',  
					R_name as 'name',  
					R_exp as 'exp',  
					R_cashReward as 'cash',  
					R_bulletReward as 'bullets',  
					R_limit as 'limit'
				FROM ranks" . $add . "
				ORDER BY R_exp"
			);

			if ($rankID == "all") {
				$rank->execute();
				return $rank->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$rank->bindParam(":id", $rankID);
				$rank->execute();
				return $rank->fetch(PDO::FETCH_ASSOC);
			}
		}

		private function validateRank($rank) {
			$errors = array();

			if (strlen($rank["name"]) < 2) {
				$errors[] = "Rank name is to short, this must be atleast 2 characters";
			}
			if ($rank["id"] == 1 && $rank["exp"] != 0) {
				$errors[] = "The first rank must be 0 exp";
			}

			return $errors;
			
		}

		public function method_newRank () {

			$rank = array();

			if (isset($this->methodData->submit)) {
				$rank = (array) $this->methodData;
				$errors = $this->validateRank($rank);
				
				if (count($errors)) {
					foreach ($errors as $error) {
						$this->html .= $this->page->buildElement("error", array("text" => $error));
					}
				} else {
					$insert = $this->db->prepare("
						INSERT INTO ranks (R_name, R_exp, R_limit, R_cashReward, R_bulletReward)  VALUES (:name, :exp, :limit, :cash, :bullets);
					");
					$insert->bindParam(":name", $this->methodData->name);
					$insert->bindParam(":exp", $this->methodData->exp);
					$insert->bindParam(":limit", $this->methodData->limit);
					$insert->bindParam(":cash", $this->methodData->cash);
					$insert->bindParam(":bullets", $this->methodData->bullets);
					$insert->execute();


					$this->html .= $this->page->buildElement("success", array("text" => "This rank has been created"));

				}

			}

			$rank["editType"] = "new";
			$this->html .= $this->page->buildElement("rankForm", $rank);
		}

		public function method_editRank () {

			if (!isset($this->methodData->id)) {
				return $this->html = $this->page->buildElement("error", array("text" => "No rank ID specified"));
			}

			$rank = $this->getRank($this->methodData->id);

			if (isset($this->methodData->submit)) {
				$rank = (array) $this->methodData;
				$errors = $this->validateRank($rank);

				if (count($errors)) {
					foreach ($errors as $error) {
						$this->html .= $this->page->buildElement("error", array("text" => $error));
					}
				} else {
					$update = $this->db->prepare("
						UPDATE ranks SET R_name = :name, R_exp = :exp, R_limit = :limit, R_cashReward = :cash, R_bulletReward = :bullets WHERE R_id = :id
					");
					$update->bindParam(":name", $this->methodData->name);
					$update->bindParam(":exp", $this->methodData->exp);
					$update->bindParam(":limit", $this->methodData->limit);
					$update->bindParam(":cash", $this->methodData->cash);
					$update->bindParam(":bullets", $this->methodData->bullets);
					$update->bindParam(":id", $this->methodData->id);
					$update->execute();

					$this->html .= $this->page->buildElement("success", array("text" => "This rank has been updated"));

				}

			}

			$rank["editType"] = "edit";
			$this->html .= $this->page->buildElement("rankForm", $rank);
		}

		public function method_deleteRank () {

			if (!isset($this->methodData->id)) {
				return $this->html = $this->page->buildElement("error", array("text" => "No rank ID specified"));
			}

			$rank = $this->getRank($this->methodData->id);

			if (!isset($rank["id"])) {
				return $this->html = $this->page->buildElement("error", array("text" => "This rank does not exist"));
			}
			if ($rank["id"] == 1) {
				return $this->html = $this->page->buildElement("error", array("text" => "You cant delete the first rank"));
			}

			if (isset($this->methodData->commit)) {

				$newRank = $this->db->prepare("
					SELECT * FROM ranks WHERE R_exp < :exp ORDER BY R_exp DESC LIMIT 0, 1
				");

				$newRank->bindParam(":exp", $rank["exp"]);
				$newRank->execute();

				$newRank = $newRank->fetch(PDO::FETCH_ASSOC);

				$delete = $this->db->prepare("
					DELETE FROM ranks WHERE R_id = :id;
					UPDATE userStats SET US_rank = :newRank WHERE US_rank = :id;
				");
				$delete->bindParam(":id", $this->methodData->id);
				$delete->bindParam(":newRank", $newRank["R_id"]);
				$delete->execute();

				header("Location: ?page=admin&module=gameSettings&action=viewRank");

			}


			$this->html .= $this->page->buildElement("rankDelete", $rank);
		}

		public function method_viewRank () {
			
			$this->html .= $this->page->buildElement("rankList", array(
				"ranks" => $this->getRank()
			));

		}

	}