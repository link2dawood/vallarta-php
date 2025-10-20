<?php
	$users_id = $_SESSION['user_id'];

$user_res = mysqli_query($con, "SELECT * FROM users WHERE id=$users_id");
$user_row = mysqli_fetch_array($user_res);
$action = $user_row['admin?'];
?>

<?php if($action=='yes'){ ?>
<div class="table-responsive">
	<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th>Title</th>
				<th>Short Description</th>
				<th>Long Description</th>
				<th>Video Type</th>
				<th>Date Created</th>
				<th>Action</th>
			</tr>
		</thead>                                    
		<tbody>
			<?php 
			//query show data from table movies
			if($_SESSION['type']=='yes'){
				$query = $con->query("SELECT * FROM movies");
			}else{
				$added_by=$_SESSION['user_id'];
				$query = $con->query("SELECT * FROM movies where added_by='$added_by'");
			}
			while($m = mysqli_fetch_array($query)) {
			?>
			<tr>
				<td><?= $m['title'];?></td>
				<td><?= $m['short_desc'];?></td>
				<td><?= $m['long_desc'];?></td>
				<td><?php if($m['video_type'] == '1'){ echo 'Embed';} elseif($m['video_type']=='2'){ echo 'Upload'; } else { echo 'no video'; } ?></td>
				<td><?= $m['date_created'];?></td>
				<td>
					<a href="movie_edit.php?id=<?= $m['movie_id'];?>" class="btn btn-primary">Edit</a>
					<?php if($_SESSION['type']=='yes'){?>
					<a href="action.php?act=del&id=<?= $m['movie_id'];?>" onclick="return confirm('Are you sure you want to delete this item?');" class="btn btn-danger">Delete</a>
					<?PHP }
					if ($m['featured'] == 0){
					   ?>
						<a href="action.php?act=pinon&id=<?= $m['movie_id'];?>" class="btn btn-primary">Pin To Featured</a>
					<?php
					}
					else{
						?>
						
						<a href="action.php?act=pinoff&id=<?= $m['movie_id'];?>" class="btn btn-warning">Remove From Featured</a>

						<?php 
					}
					?>
				</td>
			</tr>
			<?php } ?>                                  
		</tbody>
	</table>
</div>
<?php } else { ?>

<div class="table-responsive">
	<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th>Title</th>
				<th>Short Description</th>
				<th>Long Description</th>
				<th>Video Type</th>
				<th>Date Created</th>
				<th>Action</th>
			</tr>
		</thead>                                    
		<tbody>
			<?php 
			//query show data from table movies
			
				$added_by=$_SESSION['user_id'];
				$query = $con->query("SELECT * FROM movies where added_by='$added_by'");
			
			while($m = mysqli_fetch_array($query)) {
			?>
			<tr>
				<td><?= $m['title'];?></td>
				<td><?= $m['short_desc'];?></td>
				<td><?= $m['long_desc'];?></td>
				<td><?php if($m['video_type'] == '1'){ echo 'Embed';} elseif($m['video_type']=='2'){ echo 'Upload'; } else { echo 'no video'; } ?></td>
				<td><?= $m['date_created'];?></td>
				<td>
					<a href="movie_edit.php?id=<?= $m['movie_id'];?>" class="btn btn-primary">Edit</a>
				</td>
			</tr>
			<?php } ?>                                  
		</tbody>
	</table>
</div>

<?php } ?>