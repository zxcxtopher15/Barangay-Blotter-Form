<?php
// config.php — shared settings for your E-Blotter app

// Point to your running FastAPI service:
putenv('ML_API_URL=http://127.0.0.1:8000');

// Use the SAME key you set in your FastAPI .env (ML_API_KEY=...):
putenv('ML_API_KEY=ml_MrLAGxeJTYWimB3YeayI1z1Z0UjI8dxsmZ_9zQubyp0');



//curl -s http://127.0.0.1:8000/labels -H "X-API-Key: ml_MrLAGxeJTYWimB3YeayI1z1Z0UjI8dxsmZ_9zQubyp0"



