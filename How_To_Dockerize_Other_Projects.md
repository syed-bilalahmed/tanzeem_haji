# How to Dockerize Your Next Project

You can reuse the exact same setup for any other PHP project!

## Step 1: Copy Files
Copy these 2 files from your **Tanzeem** folder to your **New Project** folder:
1.  `Dockerfile`
2.  `docker-compose.yml`

## Step 2: Edit `docker-compose.yml` (IMPORTANT!)
Open the `docker-compose.yml` in your **New Project** and make 3 small changes so it doesn't fight with Tanzeem:

1.  **Change Container Names:**
    *   Change `container_name: tanzeem_web`  --> `container_name: newproject_web`
    *   Change `container_name: tanzeem_db`   --> `container_name: newproject_db`
    *   Change `container_name: tanzeem_pma`  --> `container_name: newproject_pma`

2.  **Change the Ports:** (You can't use 8080 twice!)
    *   Change `"8080:80"` --> `"8082:80"` (Web)
    *   Change `"8081:80"` --> `"8083:80"` (Database)

3.  **Change Database Name:**
    *   Change `MYSQL_DATABASE=tanzeem_db` --> `MYSQL_DATABASE=newproject_db`

## Step 3: Start it
Open terminal in the new folder and run:
```bash
docker-compose up -d
```

Now you have:
*   **Tanzeem:** http://localhost:8080
*   **New Project:** http://localhost:8082

---

## "Can I run without Apache?"
**Short Answer:** No, PHP always needs a web server (like Apache or Nginx) to show web pages.

**But with Docker:** You don't need to *install* Apache on your Windows. Docker automatically downloads a mini-Apache inside the container (that's what `FROM php:8.2-apache` means in the Dockerfile). So "without Apache" on your laptop? **Yes.** "Without Apache" anywhere? **No**, the container handles it for you.
