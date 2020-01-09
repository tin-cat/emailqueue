<?php

	namespace Emailqueue;

	class messages {
		function get_list() {
		  	global $db;
		  	global $utils;
		  	global $html;
		  	global $now;
		  	
		  	if (!$db->isanyresult())
		  		return false;
		  	
		  	$retr =
		  	"
		  		<table class=tableMessages>
		  		<tr>
		  			<th>status</th>
		  			<th>injected on</th>
		  			<th>priority</th>
		  			<th>deliver on</th>
		  			<th>delivered</th>
		  			<th>to</th>
		  			<th>subject</th>
		  			<th>options</th>
		  		</tr>
		  	";
		  	
		  	while ($row = $db->fetchrow())
		  		$rows[] = $row;
		  	
            foreach ($rows as $row) {
		  	  	$retr .=
		  	  	"
		  	  		<tr>
		  	  			<td>".
		  	  			(
		  	  				$row["is_sendingnow"]
		  	  				?
		  	  				"being sent now<br>"
		  	  				:
		  	  				""
		  	  			).
		  	  			(
		  	  				$row["send_count"] > 0
		  	  				?
		  	  				"delivered ".($row["send_count"] > 1 ? $row["send_count"]." times" : "once")."<br>"
		  	  				:
		  	  				(
		  	  					$row["is_immediate"]
		  	  					?
		  	  					"Inmediate delivery<br>"
		  	  					:
                                (
                                    $row["date_queued"]
                                    ?
                                    "scheduled delivery<br>"
                                    :
                                    "normal delivery<br>"
                                ).
                                "priority ".$row["priority"]
		  	  				)
		  	  			).
		  	  			(
		  	  				$row["is_cancelled"]
		  	  				?
		  	  				"delivery cancelled<br>"
		  	  				:
		  	  				""
		  	  			).
		  	  			(
		  	  				$row["is_blocked"]
		  	  				?
		  	  				"blocked<br>"
		  	  				:
		  	  				""
		  	  			)
		  	  			."
                        </td>		  	  			
		  	  			<td>".$utils->date_specialformat(strtotime($row["date_injected"]))."</td>
		  	  			<td>".$row["priority"]."</td>
		  	  			<td>".
							(
								$row["is_immediate"]
								?
								"Inmediately<br>"
								:
                                (
                                    $row["date_queued"]
                                    ?
                                    $utils->date_specialformat(strtotime($row["date_queued"]))
                                    :
                                    "ASAP<br>"
                                )
							).
							(
                                $row["is_sent"]
                                ?
                                "Already delivered<br>"
                                :
                                ""
							).
							(
								$row["is_blocked"]
								?
								"blocked<br>"
								:
								""
							).
							(
								$row["is_cancelled"]
								?
								"cancelled"
								:
								""
							).
						"</td>
		  	  			<td>".
							(
								!$row["send_count"]
								?
								"not yet delivered"
								:
								$utils->date_specialformat(strtotime($row["date_sent"])).
								(
									$row["send_count"] > 0
									?
									"<br>delivered ".($row["send_count"] > 1 ? $row["send_count"]." times" : "once")
									:
									""
								)
							).
						"</td>
		  	  			<td>".$row["to"]."</td>
		  	  			<td>".$utils->cuttext($row["subject"], 50, " ...")."</td>
		  	  			<td class=buttons>
								<a href=\"?a=manager&aa=view&email_id=".$row["id"]."\" class=button>view</a>
								".
								(
									!$row["is_sent"]
									&&
									!$row["is_sendingnow"]
									&&
									!$row["is_cancelled"]
									&&
									!$row["is_blocked"]
									?
									"<a href=\"javascript:confirmation('are you sure you want to BLOCK this message?', '?a=manager&aa=block&email_id=".$row["id"]."');\" class=button>block</a> "
									:
									""
								).
								(
									!$row["is_sent"]
									&&
									$row["is_blocked"]
									?
									"<a href=\"?a=manager&aa=unblock&email_id=".$row["id"]."\" class=button>unblock</a> "
									:
									""
								).
								(
									!$row["is_sent"]
									&&
									!$row["is_sendingnow"]
									&&
									!$row["is_cancelled"]
									&&
									!$row["is_blocked"]
									?
									"<a href=\"javascript:confirmation('are you sure you want to CANCEL this message?', '?a=manager&aa=cancel&email_id=".$row["id"]."');\" class=button>cancel</a> "
									:
									""
								).
								(
									!$row["is_sendingnow"]
									&&
									!$row["is_blocked"]
									&&
									(
										(!$row["is_sent"] && $row["is_cancelled"])
										||
										$row["is_sent"]
									)
									?
									"<a href=\"javascript:confirmation('are you sure you want to REQUEUE this message for inmediate delivery? It will be sent again', '?a=manager&aa=requeue&email_id=".$row["id"]."');\" class=button>requeue</a> "
									:
									""
								).
								"
		  	  			</td>
		  	  		</tr>
		  	  	";
		  	  	
		  	  	$db->query("
		  	  		select			*
		  	  		from			incidences
		  	  		where			email_id = ".$row["id"]."
		  	  		order by		date_incidence asc
		  	  	");

		  	  	if ($db->isanyresult()) {
		  	  		while ($incidence = $db->fetchrow()) {
			  	  		$retr .=
			  	  		"
			  	  			<tr>
			  	  				<td></td>
			  	  				<td>".$utils->date_specialformat(strtotime($incidence["date_incidence"]))."</td>
			  	  				<td colspan=5><img src=gfx/img/warning.gif align=absmiddle> ".$incidence["description"]."</td>
			  	  			</tr>
			  	  		";
			  	  	}
		  	  	}
		 	}
		 	
		 	$retr .=
		 	"
		 		</table>
		 	";			
			
			return $retr;
		}
	}

?>
