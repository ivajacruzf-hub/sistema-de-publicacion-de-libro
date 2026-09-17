const fs = require('fs');
const mysql = require('./node_modules/mysql2/promise');
const env = {};
for (const line of fs.readFileSync('api/.env', 'utf8').split(/\r?\n/)) {
  const separator = line.indexOf('=');
  if (separator > 0) env[line.slice(0, separator).trim()] = line.slice(separator + 1).trim().replaceAll("'", '');
}
(async () => {
  const db = await mysql.createConnection({
    host: env.DB_HOST,
    port: Number(env.DB_PORT),
    user: env.DB_USER,
    password: env.DB_PASSWORD,
    database: env.DB_NAME,
    ssl: { rejectUnauthorized: true }
  });
  await db.execute(`CREATE TABLE IF NOT EXISTS usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    nombreUsuario VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4`);
  const [rows] = await db.query('SHOW COLUMNS FROM usuarios');
  console.log(`Tabla usuarios verificada: ${rows.map(row => row.Field).join(', ')}`);
  await db.end();
})().catch(error => {
  console.error(`${error.code}: ${error.message}`);
  process.exit(1);
});