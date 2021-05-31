#!/usr/bin/python3

from pprint import pprint

import json
import mariadb
import random

CONFIGURATION_FILE = "./configuration.json"

def get_configuration():

    with open(CONFIGURATION_FILE) as configuration_file:

        return json.loads(configuration_file.read())

def main():

    configuration = get_configuration()
    credentials = configuration['database']['credentials']
    schema = configuration['database']['schema']

    with mariadb.connect(user = credentials["username"], password = credentials["password"], database = credentials["name"], host = 'localhost') as connection:

        connection.autocommit = True
        cursor = connection.cursor()
        create_table_query = "create table {:s} ({:s} int unsigned not null primary key auto_increment, {:s} text not null, {:s} int unsigned not null)"
        create_table_query = create_table_query.format(schema['table name'], schema['id'], schema['name'], schema['count'])
        cursor.execute(create_table_query)
        insert_default_data_query = "insert into {:s} values (NULL, \"banana\", {:d})".format(schema['table name'], random.randint(1, 1000))
        cursor.execute(insert_default_data_query)

if __name__ == '__main__':

    main()
