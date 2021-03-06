<?php

    class garageTemplate extends template {
    
        public $garage = '<table class="table table-condensed table-responsive">
            <tr>
                <th>Name</th>
                <th style="width:100px">Damage</th>
                <th style="width:100px">Value</th>
                <th style="width:150px">Location</th>
                <th style="width:150px">Links</th>
            </tr>
            {#unless cars}
                <tr>
                    <td colspan="5">
                        <div class="text-center">
                            <em> You have no cars</em>
                        </div>
                    </td>
                </tr>
            {/unless}
            {#each cars}
                <tr>
                    <td>{name}</td>
                    <td>{damage}</td>
                    <td>${value}</td>
                    <td>{location}</td>
                    <td>
                        <a href="?page=garage&action=sell&id={id}">Sell</a> ~
                        <a href="?page=garage&action=crush&id={id}">Crush</a> ~
                        <a href="?page=garage&action=repair&id={id}">Repair</a>
                    </td>
                </tr>
            {/each}
        </table>';
        
    }

?>