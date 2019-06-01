# **Other Possible Approaches**

I was limited on hours I could spend on this project. Thus, I did not delve too deeply into alternative routes to get Drupal working on Azure. Here are some thoughts I had on alternative routes to get this working (and potential downsides).

- **Project Structure Change:**  There is a way (I believe) to change the structure of the Drupal project to have the index.php file directly in the project root (along with all of the other directories under "docroot"). However, this is a non-standard setup and could create some confusion for the next developer to come along. It would also clutter the project root. Ultimately, I decided that keeping the Drupal setup as standard as possible was the best route to take.

- **Custom Docker Image:**  You can setup Azure to run a custom Docker image. There are a lot of pre-existing images that could be used. However, this setup would have required a decent amount of research on my part and I already found a workable solution.

- **Do It With TerraForm:**  There is a tool called TerraForm that lets you store you infrastructure as code and setup everything very precisely in a set of configuration files. You can configure it for any cloud hosting service. For this client's needs this is absolutely overkill and would take precious time we did not have.

- **Try A 3rd Party Azure Image:**  When creating the setup instructions for the client, I did notice that there were 3rd party images available to choose from in the Azure marketplace. This might have been the next best approach to take and I seriously considered this route. However, it looked like it would spin up multiple containers for this and I was a bit unsure of the costs and ease of configuration. Especially of the database, which the client had ultimate control of currently and wanted to maintain that control. Thus I decided this route might complicate things more and would rather keep it as simple for the client as possible.
