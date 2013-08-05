-- Module to manage locations, buildings, floors and rooms into Dolibarr ERP/CRM
-- Copyright (C) 2013	Jean-François Ferry	<jfefe@aternatik.fr>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

CREATE TABLE llx_place_floor
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  entity          integer,
  ref             varchar(255),
  pos             varchar(10),
  fk_building     integer NOT NULL,
  fk_user_create   integer,
  tms             timestamp
)ENGINE=innodb;