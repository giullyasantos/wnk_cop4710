# COP4710 Team Project

# PROJECT: Waste Not Kitchen (WNK)

# Project Description

Restaurant surplus food waste is a significant environment and economic problem. In 2023,
restaurants in USA generated 12.7 million tons of surplus good, most went to landfills. Your
team is to develop a **_WNK_** app for an online service “ **_Waste Not Kitchen_** ” (WNK). WNK is
both a food rescue hub as well as a charity community to feed the needy. There are five
categories of users:

1. Administrators: They can configure the
    WNK application and generate various
    summary reports.
2. Restaurants: They can sell surplus plates at
    significantly lower prices.
3. Customers: They can reserve and purchase
    surplus plates offered by the member
    restaurants.
4. Donners: They pay for the restaurant plates,
    but offer them to the needy for free.
5. Needy: They can reserve and pick up restaurant plates already been paid for by
    donners.

A restaurant, a customer, a donner, or a needy must register with “Waste Not Kitchen” though
a _registration page_ in order to participate in this community. This registration page collects
names, addresses, and phone numbers. Phone number is optional for a needy. Customers
and donners also need to provide credit card information for payments. Existing members
may _review_ and _modify_ their information at any time. After the registration, a member can
login to his account with a _password_ to use this service.

Each category of users has their own set of Web pages to interact with the WNK service as
follows:

- Restaurant: A restaurant can advertise to sell their surplus plates at WNK within a
    desired time window. They would describe the plate, its fixed price, and the quantity
    available for sale. WNK members may reserve and pick up one or more plates within
    the advertised window. The WNK tracks the inventory and closes this sell when the
    items are sold out. When a WNK member picks up a plate, the payment is
    automatically charged to the credit card. For simplicity, you do not need to implement
    the payment process.


- Customer: A customer can browse the currently available plates from the different
    restaurants, make reservations, and later visit his checkout page to confirm his orders
    when he is ready to pick them up.
- Donner: A donner can browse the currently available plates, make selections, and then
    go to the checkout page to confirm the orders and make payment. A donner does not
    pick up the plates he donated.
- Needy: A needy can browse the plates currently available for free (already paid for by
    donners), make up to two selections, and later visit his checkout page to confirm the
    orders when he is ready to pick them up.
- Administrator: An administrator can look up member information. He can also
    generate the following reports: (1) annual activity report for a restaurant, (2) annual
    purchase report for a customer or a donner, ( 3 ) annual report of free plates received by
    a needly, and ( 4 ) year-end donation report for a donner for tax purposes.

Your webpages, particularly the homepage, should be designed logically to ensure easy
navigation in your website.

**Team Work** :

The implementation of the database is done together as a team. The remaining work should
be divided as follows:

- First team member: Implements the Webpages for registration, login, and the
    restaurants, and the page for WNK members to review and edit their information.
- Second team member: Implements the Webpages for customers, the doners, and the
    needy.
- Third team member: Implements the Webpages for the administrators.

Since this is a team project, you should use only the software development environments
discussed in class. If you want to deviate from this, it should be approved by each team
member and the GTA. The GTA can assist you in MySQL, PhP, and JavaScript. You can follow
instruction at the following link to quickly install PHP, MySQL, and Apache using MAMP:
https://webcourses.ucf.edu/courses/1490410/files/118309142/download.

**Project Report and Software Submission:**

Each team prepares a project report in PDF to discuss the software development
environment, the database schema, the screenshot of each webpage, and the team activity.
The team coordinator submits this report and the software to Webcourses two days before


the demonstration. He also submits milestone reports on the behalf of the team. The
deadlines are as follows:

- Division of works: 10/31/25
- Readiness of the database server and database schema: 11/07/25
- Progress of each team member: 11/21/25
- Second report on individual progress: 11/26/25
- Project report and software submission: 12/01/25
- Demo Day: 12/3/25, HSB125 (our classroom), 4-6:50pm.

**Project Demonstration** :

Each team has 15 minutes to demonstrate the project on the team (your own) laptop
computer, starting with the first member, then the second, and finish with the third member
(as described in the Team Work section). Each member should rehearse to make sure that
you can finish your part in 5 minutes. The server does not have to be remote. Demonstration
of the database is not necessary. Each member must demonstrate the pages assigned to
him. This is important because each member will receive an individual score, not a shared
team score. If a team member cannot complete his part of the system, he should still
demonstrate what he has done successfully and explain the incomplete work.

**Grading Policy:**

Implementation: 80% Demo: 10% Report: 10% Team Coordination: 10%

The 10% for team coordination is extra credit for making timely progress with your team.
Each member will receive up to 10 extra points based on the individual progress explained
in the milestone reports.