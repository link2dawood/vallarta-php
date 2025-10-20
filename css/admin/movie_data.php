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
                                        $query = $con->query("SELECT * FROM movies");
                                        while($m = mysqli_fetch_array($query)) {
                                        ?>
                                        <tr>
                                            <td><?= $m['title'];?></td>
                                            <td><?= $m['short_desc'];?></td>
                                            <td><?= $m['long_desc'];?></td>
                                            <td><?php if($m['video_type'] == '1'){ echo 'Embed';} elseif($m['video_type']=='2'){ echo 'Upload'; } else { echo 'no video'; } ?></td>
                                            <td><?= $m['date_created'];?></td>
                                            <td><a href="?page=edit_movie&id=<?= $m['id'];?>" class="btn btn-primary">Edit</a><a href="action.php?act=del&id=<?= $m['id'];?>" onclick="return confirm('Are you sure you want to delete this item?');" class="btn btn-danger">Delete</a></td>
                                        </tr>     
                                        <?php } ?>                                  
                                    </tbody>
                                </table>
                            </div>