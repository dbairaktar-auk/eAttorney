import pandas as pd

import mariadb
import sys

from sklearn.model_selection import train_test_split
from sklearn.multioutput import MultiOutputRegressor
from sklearn.linear_model import LinearRegression
from sklearn.tree import DecisionTreeRegressor
from datetime import timedelta

# Connect to MariaDB Platform
try:
    conn = mariadb.connect(
        user="<username>",
        password="<password>",
        host="localhost",
        port=3306,
        database="eattorney_crm",
        autocommit=True
    )
except mariadb.Error as e:
    print(f"Error connecting to MariaDB Platform: {e}")
    sys.exit(1)

cur = conn.cursor()
query = "SELECT * FROM v_model_pmt_info WHERE is_first_pmt = 0 AND is_last_pmt = 0"
cur.execute(query)
names = [ x[0] for x in cur.description]
rows = cur.fetchall()
df = pd.DataFrame(rows, columns = names)

query = "SELECT * FROM v_model_pmt_info WHERE is_last_pmt = 1"
cur.execute(query)
names = [ x[0] for x in cur.description]
rows = cur.fetchall()
df_predict = pd.DataFrame(rows, columns = names)

query = "TRUNCATE TABLE predicted_next_payment"
cur.execute(query)

# Convert 'PaymentDate' to datetime format
df['payment_date'] = pd.to_datetime(df['payment_date'])

# Features and target variables
X = df[['days_since_prev_payment', 'prev_payment_amount', 'total_prev_payments', 'avg_prev_payments_amount', 'first_payment_amount', 'days_since_first_payment']]
y = df[['days_to_next_payment', 'next_payment_amount']]

# Split the data into training and testing sets
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

model = MultiOutputRegressor(LinearRegression())
#model = MultiOutputRegressor(DecisionTreeRegressor(random_state=42))
model.fit(X_train, y_train)

# Function to predict next transaction date and amount for each person
def predict_next_payment(loan_id, payment_info):
    
    # Predict next transaction date and amount
    prediction = model.predict(payment_info)

    # Extract predicted values
    predicted_days_to_next_pmt = prediction[0][0]
    predicted_pmt_amount = prediction[0][1]

    return {
        'loan_id': loan_id,
        'days_to_next_payment': round(predicted_days_to_next_pmt, 0),
        'predicted_pmt_amount': round(predicted_pmt_amount, 2)
    }


def add_prediction(cur, data):
    cur.execute("INSERT INTO predicted_next_payment(loan_id, days_to_next_payment, predicted_pmt_amount) VALUES (?, ?, ?);",
          (data['loan_id'], data['days_to_next_payment'], data['predicted_pmt_amount'])
            #(123, 5, 1234)
                )

for index, row in df_predict.iterrows():
    pmt_info = df_predict[['loan_id', 'days_since_prev_payment', 'prev_payment_amount', 'total_prev_payments', 'avg_prev_payments_amount', 'first_payment_amount', 'days_since_first_payment']].copy().iloc[[index]]
    # Example usage
    prediction_result = predict_next_payment(row['loan_id'], pmt_info[['days_since_prev_payment', 'prev_payment_amount', 'total_prev_payments', 'avg_prev_payments_amount', 'first_payment_amount', 'days_since_first_payment']])
    add_prediction(cur, prediction_result)
    
conn.close()

