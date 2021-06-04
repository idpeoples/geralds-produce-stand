#!/usr/bin/python3

from pprint import pprint

import json
import mariadb
import random

CONFIGURATION_FILE = "./configuration/configuration.json"

# get_configuration: Extracts data from the json file, returning the main dictionary object
def get_configuration():

    with open(CONFIGURATION_FILE) as configuration_file:

        return json.loads(configuration_file.read())

def main():

    # Get the configuration data and separate into two objects for readability
    configuration = get_configuration()
    credentials = configuration['database']['credentials']
    schema = configuration['database']['schema']

    with mariadb.connect(user = credentials["username"], password = credentials["password"], database = credentials["name"], host = 'localhost') as connection:

        # MariaDB documentation says autocommit default is True, testing says otherwise; set True here to avoid confusion and missed insert
        connection.autocommit = True
        cursor = connection.cursor()

        # Using formatted strings because the cursor won't accept the table name as a prepared statement
        create_table_query = "create table {:s} ({:s} int unsigned not null primary key auto_increment, {:s} varchar(256) unique not null, {:s} varchar(256) unique not null, {:s} int unsigned not null)"
        create_table_query = create_table_query.format(schema['table name'], schema['id'], schema['name'], schema['display name'], schema['count'])
        cursor.execute(create_table_query)

        # Insert a random value into the table to ensure we have something to work with
        insert_default_data_query = "insert into {:s} values (NULL, \"banana\", \"Bananas\", {:d})".format(schema['table name'], random.randint(1, 1000))
        cursor.execute(insert_default_data_query)

if __name__ == '__main__':

    main()
