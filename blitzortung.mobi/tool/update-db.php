<?php

date_default_timezone_set('Europe/Berlin');

define('SYSTEMDIR', dirname(__FILE__) . '/../');

require SYSTEMDIR . 'config.php';
require SYSTEMDIR . 'lib/utils.php';
require SYSTEMDIR . 'lib/blitzortung_data.php';

function blitzortung_sync_to_db()
{
	$dbh = new PDO('mysql:host=127.0.0.1;dbname=' . Config::db_name, Config::db_user, Config::db_pass);
	$dbh->exec('SET CHARACTER SET utf8');

	$src = new blitzortung_data(SYSTEMDIR . 'cache/txt', Config::outbound_interface);
	$src->setLogin(Config::bo_user, Config::bo_pass);

	if($src->retrieve())
	{
		$set_sql = 'owner = :owner, city = :city, bo_id = :bo_id,
			country = :country, latitude = :lat, longitude = :lon, last_signal = :last_signal,
			status = :status, client = :client, signals = :signals';

		// transfer stations:

		echo 'A';

		$stmt = $dbh->prepare('INSERT INTO stations SET idf = :idf, ' . $set_sql .
			' ON DUPLICATE KEY UPDATE ' . $set_sql);

		foreach($src->resultStations() as $stn)
		{
			$stmt->bindValue(':idf', $stn->idf);
			$stmt->bindValue(':bo_id', $stn->num_id);
			$stmt->bindValue(':owner', $stn->owner);
			$stmt->bindValue(':city', $stn->city);
			$stmt->bindValue(':country', $stn->country);
			$stmt->bindValue(':lat', $stn->lat);
			$stmt->bindValue(':lon', $stn->lon);
			$stmt->bindValue(':status', $stn->status[0]);
			$stmt->bindValue(':last_signal', (int)$stn->last_signal);
			$stmt->bindValue(':client', $stn->client);
			$stmt->bindValue(':signals', $stn->signals);

			$stmt->execute();

			$err = $stmt->errorInfo();
			if($err[1] != 0)
			{
				die($err[2]);
			}
		}

		$stmt = NULL;

		echo 'B';

		// transfer strikes:

		$stmt = $dbh->prepare('INSERT INTO strikes SET time = :time, time_actual = :time, latitude = :lat, longitude = :lon
			ON DUPLICATE KEY UPDATE latitude = :lat, longitude = :lon, time_actual = :time');

		foreach($src->resultStrikes() as $stk)
		{
			$stmt->bindValue(':time', $stk->time);
			$stmt->bindValue(':lat', $stk->lat);
			$stmt->bindValue(':lon', $stk->lon);

			$stmt->execute();

			$new_id = (int)$dbh->lastInsertId();
			if($new_id > 0)
			{
				$dbh->exec('DELETE FROM strike_stations WHERE strike_id = ' . $new_id);
				$parti_stmt = $dbh->prepare('INSERT INTO strike_stations SET strike_id = :strike_id,
					strike_time = :time, station_id = (SELECT my_id FROM stations WHERE idf = :idf)');

				foreach($stk->stations as $station_idf)
				{
					$parti_stmt->bindValue(':strike_id', $new_id);
					$parti_stmt->bindValue(':time', (int)$stk->time);
					$parti_stmt->bindValue(':idf', $station_idf);

					$parti_stmt->execute();
				}

				$parti_stmt = NULL;

				$dbh->exec('UPDATE strikes SET num_stations = (SELECT COUNT(*) FROM strike_stations sss WHERE sss.strike_id = strikes.my_id) WHERE my_id = ' . $new_id);
			}
		}

		$stmt = NULL;

		echo 'C';

		// purge strikes that have been replaced by more accurate entries:

		$first_stk = $src->resultStrikes();
		$first_stk = $first_stk[0];

		$stmt = $dbh->query('SELECT * FROM (SELECT my_id, time FROM strikes WHERE time_actual > ' . ((int)$first_stk->time) . ') x ORDER BY time DESC');

		echo 'Purge-probing ' . $stmt->rowCount() . " strikes...\n";
		$tm_start = time();
		$processed = 0;

		$r_strikes = $src->resultStrikes();
		$n_strikes = count($r_strikes);
		while($row = $stmt->fetch(\PDO::FETCH_ASSOC))
		{
			$found = false;
			for($i = $n_strikes - 1; $i >= 0; $i--)
			{
				if($r_strikes[$i]->time === $row['time'])
				{
					$found = true;
				}
			}

			if(!$found)
			{
				$stk_id = (int)$row['my_id'];
				$dbh->exec('DELETE FROM strikes WHERE my_id = ' . $stk_id);
				$dbh->exec('DELETE FROM strike_stations WHERE strike_id = ' . $stk_id);
			}

			$processed++;
			if(time() > $tm_start + 15)
			{
				echo "Purge timeout after $processed strikes.\n";
				break;
			}
		}

		$stmt = NULL;

		echo 'D';

		// update ranking:

		$stmt = $dbh->query('SELECT COUNT(*) FROM strikes WHERE time_actual >= ' . (gmtime() - 3600));
		$total_1h_strikes = (int)$stmt->fetchColumn(0);
		$stmt = NULL;

		$dbh->beginTransaction();
		$dbh->exec('DELETE FROM station_ranking');
		$dbh->exec('INSERT INTO station_ranking (station_id, strike_count, strike_ratio, signal_count, efficiency) SELECT
			my_id, (SELECT COUNT(*) FROM strike_stations ss WHERE ss.station_id = s.my_id AND strike_time >= ' . (gmtime() - 3600) . '),
			0, signals, 0 FROM stations s WHERE signals > 0');
		$dbh->exec('DELETE FROM station_ranking WHERE strike_count = 0');
		$dbh->exec('UPDATE station_ranking SET efficiency = (strike_count / ' . $total_1h_strikes . ' * 100 + strike_count / signal_count * 100) / 2,
			strike_ratio = strike_count / ' . $total_1h_strikes . ' * 100');
		$dbh->exec('UPDATE station_ranking SET efficiency = 100 WHERE efficiency > 100');
		$dbh->exec('UPDATE station_ranking SET strike_ratio = 100 WHERE strike_ratio > 100');
		$dbh->commit();

		echo 'E';
	}
	else
	{
		die('retrieve() failed!');
	}

	$dbh = NULL;
}

blitzortung_sync_to_db();

